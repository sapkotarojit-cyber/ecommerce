<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendPasswordResetCode;
use App\Mail\SendVerificationCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            'email' => ['required', 'string', 'email:rfc', 'max:254', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed', PasswordRule::defaults()],
            'password_confirmation' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $email = strtolower(trim($validated['email']));
        $code = $this->generateVerificationCode();

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => $email,
            'password' => $validated['password'],
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new SendVerificationCode($code));

        return response()->json([
            'success' => true,
            'verified' => false,
            'message' => 'Account created. Please verify your email.',
            'user' => $this->userData($user),
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
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials provided.',
            ]);
        }

        if (is_null($user->email_verified_at)) {
            $code = $this->generateVerificationCode();
            $user->forceFill([
                'verification_code' => $code,
                'verification_code_expires_at' => now()->addMinutes(10),
            ])->save();

            Mail::to($user->email)->send(new SendVerificationCode($code));

            return response()->json([
                'success' => false,
                'verified' => false,
                'email' => $email,
                'message' => 'Please verify your email first.',
                'errors' => ['email' => ['Please verify your email first.']],
            ], 403);
        }

        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $this->userData($user),
        ]);
    }

    /** POST /api/google/mobile: exchange a Google authorization code from Flutter PKCE. */
public function googleMobile(Request $request): JsonResponse
{
    $validated = $request->validate([
        'token' => ['required', 'string'],
    ]);

    $token = $validated['token'];

    // Verify token with Google's openid/tokeninfo endpoint
    $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
        'id_token' => $token,
    ]);

    if (! $response->successful()) {
        // Fallback: try checking as an access token if id_token failed
        $response = Http::withToken($token)->get('https://www.googleapis.com/oauth2/v3/userinfo');
        if (! $response->successful()) {
            return response()->json(['success' => false, 'message' => 'Invalid Google token.'], 422);
        }
        $google = $response->json();
        $googleId = $google['sub'] ?? null;
        $email = $google['email'] ?? null;
        $name = $google['name'] ?? null;
    } else {
        $google = $response->json();
        $googleId = $google['sub'] ?? null;
        $email = $google['email'] ?? null;
        $name = $google['name'] ?? null;
    }

    if (! $email) {
        return response()->json(['success' => false, 'message' => 'Could not retrieve email from Google.'], 422);
    }

    $email = strtolower(trim($email));
    $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

    if (! $user) {
        $user = User::create([
            'name' => is_string($name) ? trim($name) : 'Google User',
            'email' => $email,
            'password' => Str::random(64),
            'google_id' => $googleId,
            'email_verified_at' => now(),
        ]);
    } else {
        $updates = [];
        if (empty($user->google_id) && $googleId) $updates['google_id'] = $googleId;
        if (is_null($user->email_verified_at)) $updates['email_verified_at'] = now();
        if ($updates !== []) $user->forceFill($updates)->save();
    }

    $user->tokens()->delete();
    $token = $user->createToken('flutter-app')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Google login successful.',
        'token' => $token,
        'user' => $this->userData($user),
    ]);
}

    /** Start Socialite OAuth; Linux Flutter may supply a temporary localhost callback. */
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

            if (! is_array($callback) ||
                ($callback['scheme'] ?? null) !== 'http' ||
                ($callback['host'] ?? null) !== '127.0.0.1' ||
                ! is_int($port) || $port < 1 || $port > 65535 ||
                isset($callback['user']) || isset($callback['pass']) ||
                isset($callback['query']) || isset($callback['fragment']) ||
                ! in_array($callback['path'] ?? '', ['', '/'], true)) {
                return response('Invalid Google OAuth callback.', 400);
            }

            $state = Str::random(64);
            Cache::put('google_oauth_callback:' . $state, rtrim($callbackUrl, '/'), now()->addMinutes(5));

            return Socialite::driver('google')->stateless()->with(['state' => $state])->redirect();
        }

        // Preserve the custom-scheme flow for non-Linux clients.
        return Socialite::driver('google')->stateless()->redirect();
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
            if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->googleErrorRedirect('Invalid Google account.', $callbackUrl);
            }

            $email = strtolower(trim($email));
            $googleId = $googleUser->getId();
            if (! is_string($googleId) || trim($googleId) === '') {
                return $this->googleErrorRedirect('Google account ID was not returned.', $callbackUrl);
            }

            $name = $googleUser->getName();
            if (! is_string($name) || trim($name) === '') $name = 'Google User';

            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (! $user) {
                $user = User::create([
                    'name' => trim($name),
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

            $user->tokens()->delete();
            $token = $user->createToken('flutter-app')->plainTextToken;
            $destination = $callbackUrl ?? 'empireinnovation://oauth2redirect';

            return redirect()->away($destination . '?' . http_build_query(['token' => $token]));
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
        $validated = $this->validateStrict($request, [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        // Do not reveal whether an account exists.
        if (! $user) {
            return response()->json([
                'success' => true,
                'message' => 'If an account exists for that email address, a verification code has been sent.',
            ]);
        }

        $code = $this->generateVerificationCode();
        $user->forceFill([
            'password_reset_code' => $code,
            'password_reset_code_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::to($user->email)->send(new SendPasswordResetCode($code));

        return response()->json([
            'success' => true,
            'message' => 'If an account exists for that email address, a verification code has been sent.',
        ]);
    }

    public function verifyPasswordResetCode(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) return response()->json(['success' => false, 'message' => 'Invalid verification request.'], 422);
        if (empty($user->password_reset_code) || empty($user->password_reset_code_expires_at)) {
            return response()->json(['success' => false, 'message' => 'No active password reset code.'], 422);
        }
        if (now()->greaterThan($user->password_reset_code_expires_at)) {
            return response()->json(['success' => false, 'message' => 'Password reset code has expired.'], 422);
        }
        if (! hash_equals((string) $user->password_reset_code, (string) $validated['code'])) {
            return response()->json(['success' => false, 'message' => 'Invalid password reset code.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Verification code verified.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed', PasswordRule::defaults()],
            'password_confirmation' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) return response()->json(['success' => false, 'message' => 'Invalid password reset request.'], 422);
        if (empty($user->password_reset_code) || empty($user->password_reset_code_expires_at)) {
            return response()->json(['success' => false, 'message' => 'No active password reset code.'], 422);
        }
        if (now()->greaterThan($user->password_reset_code_expires_at)) {
            return response()->json(['success' => false, 'message' => 'Password reset code has expired.'], 422);
        }
        if (! hash_equals((string) $user->password_reset_code, (string) $validated['code'])) {
            return response()->json(['success' => false, 'message' => 'Invalid password reset code.'], 422);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'remember_token' => Str::random(60),
            'password_reset_code' => null,
            'password_reset_code_expires_at' => null,
        ])->save();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. Please login with your new password.',
        ]);
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
        $validated = $this->validateStrict($request, [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $user = User::query()->where('email', strtolower(trim($validated['email'])))->first();
        if (! $user) return response()->json(['success' => false, 'message' => 'Invalid verification request.'], 422);

        if ($user->email_verified_at) {
            $user->tokens()->delete();
            $token = $user->createToken('flutter-app')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'Email is already verified.',
                'token' => $token,
                'user' => $this->userData($user),
            ]);
        }

        if (empty($user->verification_code) || empty($user->verification_code_expires_at)) {
            return response()->json(['success' => false, 'message' => 'No active verification code.'], 422);
        }
        if (now()->greaterThan($user->verification_code_expires_at)) {
            return response()->json(['success' => false, 'message' => 'Verification code has expired.'], 422);
        }
        if (! hash_equals((string) $user->verification_code, (string) $validated['code'])) {
            return response()->json(['success' => false, 'message' => 'Invalid verification code.'], 422);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ])->save();

        $user->tokens()->delete();
        $token = $user->createToken('flutter-app')->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'token' => $token,
            'user' => $this->userData($user),
        ]);
    }

    public function resendVerificationCode(Request $request): JsonResponse
    {
        $validated = $this->validateStrict($request, [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            return response()->json([
                'success' => true,
                'message' => 'If the account exists, a verification code has been sent.',
            ]);
        }
        if ($user->email_verified_at) {
            return response()->json(['success' => false, 'message' => 'Email is already verified.'], 422);
        }

        $code = $this->generateVerificationCode();
        $user->forceFill([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ])->save();
        Mail::to($user->email)->send(new SendVerificationCode($code));

        return response()->json(['success' => true, 'message' => 'A new verification code has been sent.']);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toISOString(),
        ];
    }

    private function generateVerificationCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function configureGoogleApiRedirect(): void
    {
        $redirect = config('services.google.api_redirect');

        if (! is_string($redirect) || trim($redirect) === '') {
            throw new \RuntimeException(
                'GOOGLE_API_REDIRECT_URI is not configured.'
            );
        }

        config(['services.google.redirect' => trim($redirect)]);
    }

    private function googleErrorRedirect(string $message, ?string $callbackUrl = null)
    {
        $destination = $callbackUrl ?? 'empireinnovation://oauth2redirect';
        return redirect()->away($destination . '?' . http_build_query(['error' => $message]));
    }
}