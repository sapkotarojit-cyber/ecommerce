<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\SendVerificationCode;
use App\Models\PendingRegistration;
use App\Models\User;
use Auth0\Laravel\Auth0;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login()
    {
        return view('frontend.login');
    }

    public function vendorLogin()
    {
        if (Auth::check()) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return view('frontend.login')->with('info', 'Please log in with your vendor credentials.');
    }

    public function loginSubmit(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => ['required', 'string', 'min:8', 'max:128'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        $email = strtolower(trim($validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user || !is_string($user->password) || !Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials provided.'])->onlyInput('email');
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
                return back()->withErrors([
                    'email' => 'Your email is not verified, and we could not send a new code. Please try again shortly.',
                ])->onlyInput('email');
            }

            session(['pending_user_id' => $user->id]);
            session()->forget('pending_registration_id');

            return redirect()->route('verify.show')
                ->with('error', 'Your email is not verified. A new verification code has been sent.');
        }

        Auth::login($user, (bool) ($validated['remember'] ?? false));
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Welcome back!');
    }

    public function register()
    {
        return view('frontend.register');
    }

    public function registerSubmit(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'name' => [
                'required', 'string', 'min:2', 'max:100',
                'regex:/^[\pL\pM\pN .\'-]+$/u',
            ],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => [
                'required', 'string', 'min:8', 'max:128', 'confirmed',
                PasswordRule::defaults(),
            ],
            'password_confirmation' => ['required', 'string', 'same:password', 'max:128'],
        ]);

        $email = strtolower(trim($validated['email']));

        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account with this email already exists. Please log in or reset your password.',
            ]);
        }

        $code = $this->generateVerificationCode();
        $pending = null;

        try {
            $pending = DB::transaction(function () use ($validated, $email, $code) {
                $pending = PendingRegistration::query()
                    ->where('email', $email)
                    ->lockForUpdate()
                    ->first();

                if ($pending && $pending->last_code_sent_at &&
                    $pending->last_code_sent_at->gt(now()->subSeconds(60))) {
                    throw ValidationException::withMessages([
                        'email' => 'A verification code was recently sent. Please wait 60 seconds before registering again or use the resend option.',
                    ]);
                }

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
                    return $pending->fresh();
                }

                return PendingRegistration::create($data);
            });

            Mail::to($email)->send(new SendVerificationCode($code));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            if ($pending instanceof PendingRegistration) {
                $pending->forceFill(['last_code_sent_at' => null])->save();
            }

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['email' => 'We could not send the verification email. Please try again.']);
        }

        session()->forget('pending_user_id');
        session(['pending_registration_id' => $pending->id]);

        return redirect()->route('verify.show')
            ->with('success', 'Registration pending. Please check your email for the verification code.');
    }

    public function showVerifyForm()
    {
        if (!session()->has('pending_registration_id') && !session()->has('pending_user_id')) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in or register again.');
        }

        return view('frontend.verify-email');
    }

    public function verifyCode(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        $pendingId = session('pending_registration_id');

        if (is_int($pendingId) || (is_string($pendingId) && ctype_digit($pendingId))) {
            $result = DB::transaction(function () use ($pendingId, $validated) {
                $pending = PendingRegistration::query()
                    ->whereKey((int) $pendingId)
                    ->lockForUpdate()
                    ->first();

                if (!$pending) {
                    return ['error' => 'missing'];
                }
                if ($pending->verification_attempts >= 5) {
                    return ['error' => 'attempts'];
                }
                if (!$pending->verification_code_expires_at ||
                    now()->greaterThanOrEqualTo($pending->verification_code_expires_at)) {
                    return ['error' => 'expired'];
                }
                if (!Hash::check($validated['code'], $pending->verification_code_hash)) {
                    $pending->increment('verification_attempts');
                    return ['error' => 'invalid'];
                }

                $email = strtolower($pending->email);
                if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                    return ['error' => 'exists'];
                }

                $passwordHash = $pending->getRawOriginal('password');

                $user = new User();
                $user->name = $pending->name;
                $user->email = $email;
                $user->password = Str::random(64);
                $user->email_verified_at = now();
                $user->save();

                // The pending model already hashed the password. Copy its hash directly.
                DB::table('users')->where('id', $user->id)->update(['password' => $passwordHash]);
                $pending->delete();

                return ['user_id' => $user->id];
            });

            if (isset($result['error'])) {
                $messages = [
                    'missing' => 'Pending registration not found. Please register again.',
                    'attempts' => 'Too many incorrect attempts. Please request a new verification code.',
                    'expired' => 'The verification code has expired. Please request a new one.',
                    'invalid' => 'The verification code provided is invalid.',
                    'exists' => 'An account with this email already exists. Please log in.',
                ];

                if ($result['error'] === 'missing') {
                    session()->forget('pending_registration_id');
                }

                return back()->withErrors(['code' => $messages[$result['error']]]);
            }

            $user = User::findOrFail($result['user_id']);
            session()->forget(['pending_registration_id', 'pending_user_id']);
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('home')->with('success', 'Email verified successfully! You are now logged in.');
        }

        // Legacy verification flow for users created before pending registrations were introduced.
        $userId = session('pending_user_id');
        if (!is_int($userId) && !is_string($userId)) {
            return redirect()->route('login')->with('error', 'Session expired. Please try logging in again.');
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget('pending_user_id');
            return redirect()->route('login')->with('error', 'User not found.');
        }

        if (empty($user->verification_code) || empty($user->verification_code_expires_at)) {
            return back()->withErrors(['code' => 'No active verification code exists. Please request a new code.']);
        }
        if (now()->greaterThanOrEqualTo($user->verification_code_expires_at)) {
            return back()->withErrors(['code' => 'The verification code has expired. Please request a new one.']);
        }
        if (!hash_equals((string) $user->verification_code, (string) $validated['code'])) {
            return back()->withErrors(['code' => 'The verification code provided is invalid.']);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ])->save();

        session()->forget('pending_user_id');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Email verified successfully! You are now logged in.');
    }

    public function resendCode(Request $request)
    {
        $pendingId = session('pending_registration_id');

        if (is_int($pendingId) || (is_string($pendingId) && ctype_digit($pendingId))) {
            $pending = PendingRegistration::find((int) $pendingId);

            if (!$pending) {
                session()->forget('pending_registration_id');
                return redirect()->route('register')->with('error', 'Pending registration expired. Please register again.');
            }

            if ($pending->last_code_sent_at && $pending->last_code_sent_at->gt(now()->subSeconds(60))) {
                return back()->with('error', 'Please wait before requesting another verification code.');
            }

            $code = $this->generateVerificationCode();
            $pending->forceFill([
                'verification_code_hash' => Hash::make($code),
                'verification_code_expires_at' => now()->addMinutes(10),
                'verification_attempts' => 0,
                'last_code_sent_at' => now(),
            ])->save();

            try {
                Mail::to($pending->email)->send(new SendVerificationCode($code));
            } catch (\Throwable $e) {
                report($e);
                $pending->forceFill(['last_code_sent_at' => null])->save();
                return back()->with('error', 'We could not send the verification email. Please try again.');
            }

            return back()->with('success', 'A new verification code has been sent to your email address.');
        }

        $userId = session('pending_user_id');
        if (!is_int($userId) && !is_string($userId)) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in.');
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget('pending_user_id');
            return redirect()->route('login')->with('error', 'User not found.');
        }
        if ($user->email_verified_at) {
            session()->forget('pending_user_id');
            return redirect()->route('home')->with('success', 'Your email is already verified.');
        }

        $code = $this->generateVerificationCode();
        $user->forceFill([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ])->save();

        try {
            Mail::to($user->email)->send(new SendVerificationCode($code));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'We could not send the verification email. Please try again.');
        }

        return back()->with('success', 'A new verification code has been sent to your email address.');
    }

    public function showForgotPasswordForm()
    {
        return view('frontend.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
        ]);

        Password::broker('users')->sendResetLink([
            'email' => strtolower(trim($validated['email'])),
        ]);

        return back()->with('success', 'If an account exists for that email address, a password reset link has been sent.');
    }

    public function showResetPasswordForm(Request $request, $token = null)
    {
        if (!is_string($token) || $token === '' || strlen($token) > 512 ||
            !preg_match('/^[A-Za-z0-9]+$/', $token)) {
            return redirect()->route('password.request')->with('error', 'Please request a new password reset link.');
        }

        $validated = $this->validateStrict($request, [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
        ]);

        return view('frontend.reset-password', [
            'token' => $token,
            'email' => strtolower(trim($validated['email'])),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'token' => ['required', 'string', 'max:512', 'regex:/^[A-Za-z0-9]+$/'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => [
                'required', 'string', 'min:8', 'max:128', 'confirmed',
                PasswordRule::defaults(),
            ],
            'password_confirmation' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $email = strtolower(trim($validated['email']));
        $status = Password::broker('users')->reset([
            'email' => $email,
            'password' => $validated['password'],
            'password_confirmation' => $validated['password_confirmation'],
            'token' => $validated['token'],
        ], function ($user, $password) {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Your password has been reset successfully. Please log in.');
        }

        return back()
            ->withInput($request->except(['password', 'password_confirmation', 'token']))
            ->withErrors(['email' => __($status)]);
    }

    public function auth0Redirect()
    {
        /** @var \Auth0\Laravel\Auth0 $auth0 */
        $auth0 = app(Auth0::class);
        return redirect()->away($auth0->login());
    }

    public function auth0Callback()
    {
        try {
            /** @var \Auth0\Laravel\Auth0 $auth0 */
            $auth0 = app(Auth0::class);
            $userInfo = $auth0->getUser();

            if (!is_array($userInfo) || empty($userInfo['email']) ||
                !is_string($userInfo['email']) ||
                !filter_var($userInfo['email'], FILTER_VALIDATE_EMAIL)) {
                return redirect()->route('login')->with('error', 'Invalid response from Auth0.');
            }

            $email = strtolower(trim($userInfo['email']));
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if (!$user) {
                $name = $userInfo['name'] ?? $userInfo['nickname'] ?? 'Auth0 User';
                if (!is_string($name) || trim($name) === '' || mb_strlen($name) > 100) {
                    $name = 'Auth0 User';
                }

                $user = User::create([
                    'name' => trim($name),
                    'email' => $email,
                    'password' => Str::random(64),
                    'email_verified_at' => now(),
                ]);
            } elseif (is_null($user->email_verified_at)) {
                if (($userInfo['email_verified'] ?? false) !== true) {
                    return redirect()->route('login')->with('error', 'Please verify your email before signing in.');
                }
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            PendingRegistration::query()->where('email', $email)->delete();

            Auth::login($user, true);
            request()->session()->regenerate();

            return redirect()->route('home')->with('success', 'Logged in via Auth0!');
        } catch (\Throwable $e) {
            Log::error('Auth0 Callback Error', ['message' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
        }
    }

    public function redirect()
    {
        try {
            return Socialite::driver('google')->stateless()->redirect();
        } catch (\Throwable $e) {
            Log::error('Google redirect error', ['message' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Unable to connect to Google. Please try again.');
        }
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $googleEmail = $googleUser?->getEmail();

            if (!$googleUser || !is_string($googleEmail) ||
                !filter_var($googleEmail, FILTER_VALIDATE_EMAIL)) {
                return redirect()->route('login')->with('error', 'Invalid response from Google.');
            }

            $email = strtolower(trim($googleEmail));
            $googleId = $googleUser->getId();

            if (!is_string($googleId) || trim($googleId) === '') {
                return redirect()->route('login')->with('error', 'Google did not return a valid account ID.');
            }

            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

            if (!$user) {
                $name = $googleUser->getName() ?? 'Google User';
                if (!is_string($name) || trim($name) === '' || mb_strlen($name) > 100) {
                    $name = 'Google User';
                }

                $user = User::create([
                    'name' => trim($name),
                    'email' => $email,
                    'password' => Str::random(64),
                    'google_id' => $googleId,
                    'email_verified_at' => now(),
                ]);
            } else {
                $updates = [];
                if (empty($user->google_id)) {
                    $updates['google_id'] = $googleId;
                }
                if (is_null($user->email_verified_at)) {
                    $updates['email_verified_at'] = now();
                }
                if ($updates !== []) {
                    $user->forceFill($updates)->save();
                }
            }

            PendingRegistration::query()->where('email', $email)->delete();

            Auth::login($user, true);
            request()->session()->regenerate();

            return redirect()->route('home')->with('success', 'Welcome back, ' . $user->name . '!');
        } catch (\Throwable $e) {
            Log::error('Google callback error', ['message' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully!');
    }


    private function generateVerificationCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}