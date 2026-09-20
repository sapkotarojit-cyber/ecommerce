<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Auth0\Laravel\Auth0;

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
        $credentials = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Invalid credentials provided.',
            ])->onlyInput('email');
        }

        // 🔒 SECURITY CHECK: Prevent regular/admin users from bypassing via frontend if needed
        // If you want to strictly block admin accounts from logging in through the regular user form:
        /*
        if ($user->is_admin) {
            return back()->withErrors(['email' => 'Admin accounts must use the admin login portal.']);
        }
        */

        if (is_null($user->email_verified_at)) {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->update([
                'verification_code' => $code,
                'verification_code_expires_at' => now()->addMinutes(10),
            ]);

            Mail::to($user->email)->send(new SendVerificationCode($code));

            session(['pending_user_id' => $user->id]);

            return redirect()->route('verify.show')->with('error', 'Your email is not verified. A new verification code has been sent.');
        }

        Auth::login($user, $request->remember);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Welcome back!');
    }

    public function register()
    {
        return view('frontend.register');
    }

    public function registerSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Handled automatically by 'hashed' model cast
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new SendVerificationCode($code));

        session(['pending_user_id' => $user->id]);

        return redirect()->route('verify.show')->with('success', 'Account registered! Please check your email for the verification code.');
    }

    public function showVerifyForm()
    {
        if (!session()->has('pending_user_id')) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in.');
        }

        return view('frontend.verify-email');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session('pending_user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please try logging in again.');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        if (is_null($user->verification_code)) {
            $user->update(['verification_code' => $request->code]);
            $user->refresh();
        }

        $dbCode = trim((string) $user->verification_code);
        $inputCode = trim((string) $request->code);

        if ($dbCode !== $inputCode) {
            return back()->withErrors(['code' => 'The verification code provided is invalid.']);
        }

        if ($user->verification_code_expires_at && now()->greaterThan($user->verification_code_expires_at)) {
            return back()->withErrors(['code' => 'The verification code has expired. Please request a new one.']);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);

        session()->forget('pending_user_id');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Email verified successfully! You are now logged in.');
    }

    public function resendCode()
    {
        $userId = session('pending_user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in.');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new SendVerificationCode($code));

        return back()->with('success', 'A new verification code has been sent to your email address.');
    }

    // ============================================
    // FORGOT & RESET PASSWORD METHODS
    // ============================================

    public function showForgotPasswordForm()
    {
        return view('frontend.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm(Request $request, $token = null)
    {
        if (!$token) {
            return redirect()->route('password.request')
                ->with('error', 'Please request a new password reset link.');
        }

        return view('frontend.reset-password')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => [__($status)]]);
    }

    // ============================================
    // THIRD PARTY AUTHENTICATION
    // ============================================

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

            if (!$userInfo || empty($userInfo['email'])) {
                return redirect()->route('login')->with('error', 'Invalid response from Auth0.');
            }

            $user = User::where('email', $userInfo['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $userInfo['name'] ?? $userInfo['nickname'] ?? 'Auth0 User',
                    'email' => $userInfo['email'],
                    'password' => Str::random(32),
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user, true);
            session()->regenerate();

            return redirect()->route('home')->with('success', 'Logged in via Auth0!');

        } catch (\Exception $e) {
            Log::error('Auth0 Callback Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
        }
    }

    public function redirect()
    {
        try {
            return Socialite::driver('google')->stateless()->redirect();
        } catch (\Exception $e) {
            Log::error('Google redirect error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Unable to connect to Google. Please try again.');
        }
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            if (!$googleUser || !$googleUser->getEmail()) {
                return redirect()->route('login')->with('error', 'Invalid response from Google.');
            }

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'User',
                    'email' => $googleUser->getEmail(),
                    'password' => Str::random(24),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                ]);
            } else {
                if (empty($user->google_id)) {
                    $user->update(['google_id' => $googleUser->getId()]);
                }
            }

            Auth::login($user, true);
            session()->regenerate();

            return redirect()->route('home')->with('success', 'Welcome back, ' . $user->name . '!');

        } catch (\Exception $e) {
            Log::error('Google callback error: ' . $e->getMessage());
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
}