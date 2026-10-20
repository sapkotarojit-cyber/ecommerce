<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendPasswordResetCode;
use App\Mail\SendVerificationCode;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\pL\pM\pN .\'-]+$/u'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed', PasswordRule::defaults()],
            'password_confirmation' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $email = strtolower(trim($validated['email']));
        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account with this email already exists. Please log in or reset your password.',
            ]);
        }

        $code = $this->generateVerificationCode();
        DB::transaction(function () use ($validated, $email, $code): void {
            if (User::query()->whereRaw('LOWER(email) = ?', [$email])->lockForUpdate()->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'An account with this email already exists. Please log in or reset your password.',
                ]);
            }

            $pending = PendingRegistration::query()->where('email', $email)->lockForUpdate()->first();
            $data = [
                'name' => trim($validated['name']),
                'email' => $email,
                'password' => $validated['password'],
                'verification_code_hash' => Hash::make($code),
                'verification_code_expires_at' => now()->addMinutes(10),
                'verification_attempts' => 0,
                'last_code_sent_at' => now(),
            ];

            if ($pending) {
                $pending->fill($data)->save();
            } else {
                PendingRegistration::create($data);
            }
        });

        try {
            Mail::to($email)->send(new SendVerificationCode($code));
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => 'We could not send the verification email. Please try again using the resend-code option.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'verified' => false,
            'message' => 'Registration pending. Please verify your email.',
            'user' => ['name' => trim($validated['name']), 'email' => $email],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'email' => ['bail', 'required', 'string', 'email:rfc', 'max:254'],
            'password' => ['bail', 'required', 'string', 'min:8', 'max:128'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user || ! is_string($user->password) || ! Hash::check($validated['password'], $user->password)) {
            // If the account has not yet been promoted from pending registration, report verification state.
            $pending = PendingRegistration::query()->where('email', $email)->first();
            if ($pending && Hash::check($validated['password'], $pending->password)) {
                return response()->json([
                    'success' => false,
                    'verified' => false,
                    'email' => $email,
                    'message' => 'Please verify your email first.',
                    'errors' => ['email' => ['Please verify your email first.']],
                ], 403);
            }
            throw ValidationException::withMessages(['email' => 'Invalid credentials provided.']);
        }

        if (is_null($user->email_verified_at)) {
            $code = $this->generateVerificationCode();
            $user->forceFill([
                'verification_code' => $code,
                'verification_code_expires_at' => now()->addMinutes(10),
            ])->save();
            try {
                Mail::to($user->email)->send(new SendVerificationCode($code));
            } catch (\Throwable $e) {
                report($e);
                return response()->json(['success' => false, 'verified' => false, 'message' => 'We could not send the verification email. Please try again.'], 503);
            }
            return response()->json([
                'success' => false,
                'verified' => false,
                'email' => $email,
                'message' => 'Please verify your email first.',
                'errors' => ['email' => ['Please verify your email first.']],
            ], 403);
        }

        $token = $user->createToken('flutter-app')->plainTextToken;
        return response()->json(['success' => true, 'message' => 'Login successful.', 'token' => $token, 'user' => $this->userData($user)]);
    }

    /** POST /api/google/mobile: verify a Google ID token or access token. */
    public function googleMobile(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, ['token' => ['required', 'string', 'max:8192']]);
        $token = $validated['token'];

        $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $token]);
        if ($response->successful()) {
            $google = $response->json();
            $configuredClientId = config('services.google.client_id');
            $audience = $google['aud'] ?? null;
            if (is_string($configuredClientId) && $configuredClientId !== '' && $audience !== $configuredClientId) {
                return response()->json(['success' => false, 'message' => 'Google token was issued for a different application.'], 422);
            }
            $emailVerified = filter_var($google['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        } else {
            $response = Http::timeout(10)->withToken($token)->get('https://www.googleapis.com/oauth2/v3/userinfo');
            if (! $response->successful()) {
                return response()->json(['success' => false, 'message' => 'Invalid Google token.'], 422);
            }
            $google = $response->json();
            $emailVerified = filter_var($google['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        $googleId = $google['sub'] ?? null;
        $email = $google['email'] ?? null;
        $name = $google['name'] ?? null;
        if (! is_string($googleId) || trim($googleId) === '' || ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Could not retrieve a valid Google identity.'], 422);
        }
        if (! $emailVerified) {
            return response()->json(['success' => false, 'message' => 'Your Google email address must be verified before signing in.'], 403);
        }

        $email = strtolower(trim($email));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) {
            $user = User::create([
                'name' => is_string($name) && trim($name) !== '' ? trim($name) : 'Google User',
                'email' => $email,
                'password' => Str::random(64),
                'google_id' => $googleId,
                'email_verified_at' => now(),
            ]);
        } else {
            $updates = [];
            if (empty($user->google_id)) $updates['google_id'] = $googleId;
            if (is_null($user->email_verified_at)) $updates['email_verified_at'] = now();
            if ($updates !== []) $user->forceFill($updates)->save();
        }
        PendingRegistration::query()->where('email', $email)->delete();
        $user->tokens()->delete();
        $plainTextToken = $user->createToken('flutter-app')->plainTextToken;
        return response()->json(['success' => true, 'message' => 'Google login successful.', 'token' => $plainTextToken, 'user' => $this->userData($user)]);
    }

    /** Start Socialite OAuth and ask Google to reauthenticate the account. */
    public function googleRedirect(Request $request)
    {
        $this->configureGoogleApiRedirect();
        $callbackUrl = $request->query('callback');

        if ($callbackUrl !== null) {
            if (! is_string($callbackUrl) || strlen($callbackUrl) > 2048) {
                return response('Invalid Google OAuth callback.', 400);
            }

            $callback = parse_url($callbackUrl);
            $port = is_array($callback) ? ($callback['port'] ?? null) : null;

            if (
                ! is_array($callback) ||
                ($callback['scheme'] ?? null) !== 'http' ||
                ($callback['host'] ?? null) !== '127.0.0.1' ||
                ! is_int($port) ||
                $port < 1 ||
                $port > 65535 ||
                isset($callback['user']) ||
                isset($callback['pass']) ||
                isset($callback['query']) ||
                isset($callback['fragment']) ||
                ! in_array($callback['path'] ?? '', ['', '/'], true)
            ) {
                return response('Invalid Google OAuth callback.', 400);
            }

            $state = Str::random(64);
            Cache::put(
                'google_oauth_callback:' . $state,
                rtrim($callbackUrl, '/'),
                now()->addMinutes(5)
            );

            return Socialite::driver('google')
                ->stateless()
                ->with([
                    'state' => $state,
                    'prompt' => 'login',
                ])
                ->redirect();
        }

        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'login'])
            ->redirect();
    }

    /** Complete Google OAuth and return the result to Flutter. */
    public function googleCallback(Request $request)
    {
        $this->configureGoogleApiRedirect();
        $state = $request->query('state');
        $callbackUrl = null;
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            if (is_string($state) && strlen($state) === 64 && preg_match('/^[A-Za-z0-9]+$/', $state)) {
                $storedCallback = Cache::pull('google_oauth_callback:' . $state);
                if (is_string($storedCallback)) $callbackUrl = $storedCallback;
            }
            $email = $googleUser->getEmail();
            if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->googleErrorRedirect('Invalid Google account.', $callbackUrl);
            $email = strtolower(trim($email));
            $googleId = $googleUser->getId();
            if (! is_string($googleId) || trim($googleId) === '') return $this->googleErrorRedirect('Google account ID was not returned.', $callbackUrl);
            $name = $googleUser->getName();
            if (! is_string($name) || trim($name) === '') $name = 'Google User';
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (! $user) {
                $user = User::create(['name' => trim($name), 'email' => $email, 'password' => Str::random(64), 'google_id' => $googleId, 'email_verified_at' => now()]);
            } else {
                $updates = [];
                if (empty($user->google_id)) $updates['google_id'] = $googleId;
                if (is_null($user->email_verified_at)) $updates['email_verified_at'] = now();
                if ($updates !== []) $user->forceFill($updates)->save();
            }
            PendingRegistration::query()->where('email', $email)->delete();
            $user->tokens()->delete();
            $plainTextToken = $user->createToken('flutter-app')->plainTextToken;
            $destination = $callbackUrl ?? 'empireinnovation://oauth2redirect';
            return redirect()->away($destination . '?' . http_build_query(['token' => $plainTextToken]));
        } catch (\Throwable $e) {
            report($e);
            if ($callbackUrl === null && is_string($state) && strlen($state) === 64 && preg_match('/^[A-Za-z0-9]+$/', $state)) {
                $storedCallback = Cache::pull('google_oauth_callback:' . $state);
                if (is_string($storedCallback)) $callbackUrl = $storedCallback;
            }
            return $this->googleErrorRedirect('Google authentication failed.', $callbackUrl);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, ['email' => ['required', 'string', 'email:rfc', 'max:254']]);
        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) return response()->json(['success' => true, 'message' => 'If an account exists for that email address, a verification code has been sent.']);
        $code = $this->generateVerificationCode();
        $user->forceFill(['password_reset_code' => $code, 'password_reset_code_expires_at' => now()->addMinutes(10)])->save();
        try {
            Mail::to($user->email)->send(new SendPasswordResetCode($code));
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'We could not send the password reset email. Please try again.'], 503);
        }
        return response()->json(['success' => true, 'message' => 'If an account exists for that email address, a verification code has been sent.']);
    }

    public function verifyPasswordResetCode(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, ['email' => ['required', 'string', 'email:rfc', 'max:254'], 'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/']]);
        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user || empty($user->password_reset_code) || empty($user->password_reset_code_expires_at)) return response()->json(['success' => false, 'message' => 'Invalid verification request.'], 422);
        if (now()->greaterThanOrEqualTo($user->password_reset_code_expires_at)) return response()->json(['success' => false, 'message' => 'Password reset code has expired.'], 422);
        if (! hash_equals((string) $user->password_reset_code, (string) $validated['code'])) return response()->json(['success' => false, 'message' => 'Invalid password reset code.'], 422);
        return response()->json(['success' => true, 'message' => 'Verification code verified.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, ['email' => ['required', 'string', 'email:rfc', 'max:254'], 'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'], 'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed', PasswordRule::defaults()], 'password_confirmation' => ['required', 'string', 'min:8', 'max:128']]);
        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user || empty($user->password_reset_code) || empty($user->password_reset_code_expires_at)) return response()->json(['success' => false, 'message' => 'Invalid password reset request.'], 422);
        if (now()->greaterThanOrEqualTo($user->password_reset_code_expires_at)) return response()->json(['success' => false, 'message' => 'Password reset code has expired.'], 422);
        if (! hash_equals((string) $user->password_reset_code, (string) $validated['code'])) return response()->json(['success' => false, 'message' => 'Invalid password reset code.'], 422);
        $user->forceFill(['password' => $validated['password'], 'remember_token' => Str::random(60), 'password_reset_code' => null, 'password_reset_code_expires_at' => null])->save();
        $user->tokens()->delete();
        return response()->json(['success' => true, 'message' => 'Password reset successfully. Please login with your new password.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'user' => $this->userData($request->user())]);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, ['email' => ['required', 'string', 'email:rfc', 'max:254'], 'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/']]);
        $email = strtolower(trim($validated['email']));
        $code = $validated['code'];

        $result = DB::transaction(function () use ($email, $code): array {
            $pending = PendingRegistration::query()->where('email', $email)->lockForUpdate()->first();
            if ($pending) {
                if ($pending->verification_attempts >= 5) return ['error' => 'attempts'];
                if (now()->greaterThanOrEqualTo($pending->verification_code_expires_at)) return ['error' => 'expired'];
                if (! Hash::check($code, $pending->verification_code_hash)) {
                    $pending->increment('verification_attempts');
                    return ['error' => 'invalid'];
                }
                if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) return ['error' => 'exists'];
                $passwordHash = $pending->getRawOriginal('password');
                $user = User::create(['name' => $pending->name, 'email' => $pending->email, 'password' => Str::random(64), 'email_verified_at' => now()]);
                DB::table('users')->where('id', $user->id)->update(['password' => $passwordHash]);
                $pending->delete();
                return ['user_id' => $user->id];
            }

            // Preserve verification for legacy users created before pending_registrations existed.
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->lockForUpdate()->first();
            if (! $user) return ['error' => 'not_found'];
            if ($user->email_verified_at) return ['user_id' => $user->id];
            if (empty($user->verification_code) || empty($user->verification_code_expires_at)) return ['error' => 'no_code'];
            if (now()->greaterThanOrEqualTo($user->verification_code_expires_at)) return ['error' => 'expired'];
            if (! hash_equals((string) $user->verification_code, (string) $code)) return ['error' => 'invalid'];
            $user->forceFill(['email_verified_at' => now(), 'verification_code' => null, 'verification_code_expires_at' => null])->save();
            return ['user_id' => $user->id];
        });

        if (isset($result['error'])) {
            $error = $result['error'];
            $messages = ['attempts' => 'Too many incorrect attempts. Please request a new code.', 'expired' => 'Verification code has expired. Please request a new one.', 'invalid' => 'Invalid verification code.', 'exists' => 'An account with this email already exists. Please log in.', 'not_found' => 'Invalid verification request.', 'no_code' => 'No active verification code.'];
            return response()->json(['success' => false, 'message' => $messages[$error] ?? 'Invalid verification request.'], $error === 'attempts' ? 429 : 422);
        }

        $user = User::query()->findOrFail($result['user_id']);
        $user->tokens()->delete();
        $plainTextToken = $user->createToken('flutter-app')->plainTextToken;
        return response()->json(['success' => true, 'message' => 'Email verified successfully.', 'token' => $plainTextToken, 'user' => $this->userData($user)]);
    }

    public function resendVerificationCode(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, ['email' => ['required', 'string', 'email:rfc', 'max:254']]);
        $email = strtolower(trim($validated['email']));
        $pending = PendingRegistration::query()->where('email', $email)->first();

        if ($pending) {
            if ($pending->last_code_sent_at && $pending->last_code_sent_at->gt(now()->subSeconds(60))) {
                return response()->json(['success' => false, 'message' => 'Please wait before requesting another code.'], 429);
            }
            $code = $this->generateVerificationCode();
            $pending->forceFill(['verification_code_hash' => Hash::make($code), 'verification_code_expires_at' => now()->addMinutes(10), 'verification_attempts' => 0, 'last_code_sent_at' => now()])->save();
            try {
                Mail::to($pending->email)->send(new SendVerificationCode($code));
            } catch (\Throwable $e) {
                report($e);
                // Permit retry after mail failure, while the previous code remains invalidated.
                $pending->forceFill(['last_code_sent_at' => null])->save();
                return response()->json(['success' => false, 'message' => 'We could not send the verification email. Please try again.'], 503);
            }
            return response()->json(['success' => true, 'verified' => false, 'message' => 'A new verification code has been sent.']);
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) return response()->json(['success' => true, 'message' => 'If the account exists, a verification code has been sent.']);
        if ($user->email_verified_at) return response()->json(['success' => false, 'message' => 'Email is already verified.'], 422);
        $code = $this->generateVerificationCode();
        $user->forceFill(['verification_code' => $code, 'verification_code_expires_at' => now()->addMinutes(10)])->save();
        try {
            Mail::to($user->email)->send(new SendVerificationCode($code));
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['success' => false, 'message' => 'We could not send the verification email. Please try again.'], 503);
        }
        return response()->json(['success' => true, 'message' => 'A new verification code has been sent.']);
    }


    private function userData(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'email_verified_at' => $user->email_verified_at?->toISOString()];
    }

    private function generateVerificationCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function configureGoogleApiRedirect(): void
    {
        $redirect = config('services.google.api_redirect');
        if (! is_string($redirect) || trim($redirect) === '') throw new \RuntimeException('GOOGLE_API_REDIRECT_URI is not configured.');
        config(['services.google.redirect' => trim($redirect)]);
    }

    private function googleErrorRedirect(string $message, ?string $callbackUrl = null)
    {
        $destination = $callbackUrl ?? 'empireinnovation://oauth2redirect';
        return redirect()->away($destination . '?' . http_build_query(['error' => $message]));
    }
}