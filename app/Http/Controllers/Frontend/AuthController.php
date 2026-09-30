<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\SendVerificationCode;
use App\Models\User;
use Auth0\Laravel\Auth0;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

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

        return view('frontend.login')->with(
            'info',
            'Please log in with your vendor credentials.'
        );
    }

   public function loginSubmit(Request $request)
{
        $validated = $this->validateStrict($request, [  
            'email' => [
            'required',
            'string',
            'email:rfc',
            'max:254',
        ],

        'password' => [
            'required',
            'string',
            'min:8',
            'max:128',
        ],

        'remember' => [
            'sometimes',
            'boolean',
        ],
    ]);

    $email = strtolower(
        trim($validated['email'])
    );

    $user = User::query()
    ->whereRaw(
        'LOWER(email) = ?',
        [$email]
    )
    ->first();

    /*
    |--------------------------------------------------------------------------
    | Invalid credentials
    |--------------------------------------------------------------------------
    |
    | Do not reveal whether the email exists.
    |
    */

    if (
        !$user ||
        !is_string($user->password) ||
        !Hash::check(
            $validated['password'],
            $user->password
        )
    ) {
        return back()
            ->withErrors([
                'email' =>
                    'Invalid credentials provided.',
            ])
            ->onlyInput('email');
    }

    /*
    |--------------------------------------------------------------------------
    | Email verification
    |--------------------------------------------------------------------------
    */

    if (
        is_null(
            $user->email_verified_at
        )
    ) {
        $code =
            $this->generateVerificationCode();

        $user->forceFill([
            'verification_code' =>
                $code,

            'verification_code_expires_at' =>
                now()->addMinutes(10),
        ])->save();

        Mail::to(
            $user->email
        )->send(
            new SendVerificationCode(
                $code
            )
        );

        session([
            'pending_user_id' =>
                $user->id,
        ]);

        return redirect()
            ->route('verify.show')
            ->with(
                'error',
                'Your email is not verified. A new verification code has been sent.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    Auth::login(
        $user,
        (bool) (
            $validated['remember']
            ?? false
        )
    );

    /*
    |--------------------------------------------------------------------------
    | Prevent session fixation
    |--------------------------------------------------------------------------
    */

    $request
        ->session()
        ->regenerate();

    return redirect()
        ->intended(
            route('home')
        )
        ->with(
            'success',
            'Welcome back!'
        );
}

    /*
    |--------------------------------------------------------------------------
    | REGISTRATION
    |--------------------------------------------------------------------------
    */

    public function register()
    {
        return view('frontend.register');
    }

   public function registerSubmit(Request $request)
{
    Log::info('REGISTRATION REQUEST RECEIVED', [
        'email' => $request->input('email'),
        'fields' => array_keys($request->all()),
    ]);

    $validated = $this->validateStrict($request, [
    'name' => [
        'required',
        'string',
        'min:2',
        'max:100',
        'regex:/^[\pL\pM\pN .\'-]+$/u',
    ],

    'email' => [
        'required',
        'string',
        'email:rfc',
        'max:254',
        'unique:users,email',
    ],

    'password' => [
        'required',
        'string',
        'min:8',
        'max:128',
        'confirmed',
        PasswordRule::defaults(),
    ],

    'password_confirmation' => [
        'required',
        'string',
        'same:password',
        'max:128',
    ],
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

        Mail::to($user->email)
            ->send(new SendVerificationCode($code));

        session([
            'pending_user_id' => $user->id,
        ]);

        return redirect()
            ->route('verify.show')
            ->with(
                'success',
                'Account registered! Please check your email for the verification code.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EMAIL VERIFICATION
    |--------------------------------------------------------------------------
    */

    public function showVerifyForm()
    {
        if (!session()->has('pending_user_id')) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Session expired. Please log in.'
                );
        }

        return view('frontend.verify-email');
    }

    public function verifyCode(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/',
            ],
        ]);

        $userId = session('pending_user_id');

        if (
            !is_int($userId) &&
            !is_string($userId)
        ) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Session expired. Please try logging in again.'
                );
        }

        $user = User::find($userId);

        if (!$user) {
            session()->forget('pending_user_id');

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'User not found.'
                );
        }

        /*
         * Never create a database verification code
         * from user-submitted input.
         */
        if (
            empty($user->verification_code) ||
            empty($user->verification_code_expires_at)
        ) {
            return back()
                ->withErrors([
                    'code' => 'No active verification code exists. Please request a new code.',
                ]);
        }

        if (
            now()->greaterThan(
                $user->verification_code_expires_at
            )
        ) {
            return back()
                ->withErrors([
                    'code' => 'The verification code has expired. Please request a new one.',
                ]);
        }

        /*
         * Constant-time comparison
         */
        $dbCode = (string) $user->verification_code;
        $inputCode = (string) $validated['code'];

        if (!hash_equals($dbCode, $inputCode)) {
            return back()
                ->withErrors([
                    'code' => 'The verification code provided is invalid.',
                ]);
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ])->save();

        session()->forget('pending_user_id');

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Email verified successfully! You are now logged in.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | RESEND VERIFICATION CODE
    |--------------------------------------------------------------------------
    */

    public function resendCode(Request $request)
    {
        $userId = session('pending_user_id');

        if (
            !is_int($userId) &&
            !is_string($userId)
        ) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Session expired. Please log in.'
                );
        }

        $user = User::find($userId);

        if (!$user) {
            session()->forget('pending_user_id');

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'User not found.'
                );
        }

        $code = $this->generateVerificationCode();

        $user->forceFill([
            'verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::to($user->email)
            ->send(new SendVerificationCode($code));

        return back()->with(
            'success',
            'A new verification code has been sent to your email address.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORGOT PASSWORD
    |--------------------------------------------------------------------------
    */

    public function showForgotPasswordForm()
    {
        return view('frontend.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $this->validateStrict($request, [
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:254',
            ],
        ]);

        $email = strtolower(trim($validated['email']));

        Password::broker('users')->sendResetLink([
            'email' => $email,
        ]);

        /*
         * Always return a generic response.
         * This prevents account enumeration.
         */
        return back()->with(
            'success',
            'If an account exists for that email address, a password reset link has been sent.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESET PASSWORD FORM
    |--------------------------------------------------------------------------
    */

    public function showResetPasswordForm(
        Request $request,
        $token = null
    ) {
        /*
         * Strict token validation
         */
        if (
            !is_string($token) ||
            $token === '' ||
            strlen($token) > 512 ||
            !preg_match('/^[A-Za-z0-9]+$/', $token)
        ) {
            return redirect()
                ->route('password.request')
                ->with(
                    'error',
                    'Please request a new password reset link.'
                );
        }

        /*
         * Strict query-string validation
         */
        $validated = $this->validateStrict($request, [
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:254',
            ],
        ]);

        $email = strtolower(trim($validated['email']));

        return view('frontend.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RESET PASSWORD
    |--------------------------------------------------------------------------
    */
public function resetPassword(Request $request)
{
    $validated = $this->validateStrict($request, [
        'token' => [
            'required',
            'string',
            'max:512',
            'regex:/^[A-Za-z0-9]+$/',
        ],

        'email' => [
            'required',
            'string',
            'email:rfc',
            'max:254',
        ],

        'password' => [
            'required',
            'string',
            'min:8',
            'max:128',
            'confirmed',
            PasswordRule::defaults(),
        ],

        // ADD THIS
        'password_confirmation' => [
            'required',
            'string',
            'min:8',
            'max:128',
        ],
    ]);

    $email = strtolower(trim($validated['email']));

    $status = Password::broker('users')->reset(
        [
            'email' => $email,
            'password' => $validated['password'],
            'password_confirmation' => $validated['password_confirmation'],
            'token' => $validated['token'],
        ],
        function ($user, $password) {

            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return redirect()
            ->route('login')
            ->with(
                'success',
                'Your password has been reset successfully. Please log in.'
            );
    }

    return back()
        ->withInput()
        ->withErrors([
            'email' => __($status),
        ]);
}

    /*
    |--------------------------------------------------------------------------
    | AUTH0
    |--------------------------------------------------------------------------
    */

    public function auth0Redirect()
    {
        /** @var \Auth0\Laravel\Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        return redirect()->away(
            $auth0->login()
        );
    }

    public function auth0Callback()
    {
        try {

            /** @var \Auth0\Laravel\Auth0 $auth0 */
            $auth0 = app(Auth0::class);

            $userInfo = $auth0->getUser();

            if (
                !is_array($userInfo) ||
                empty($userInfo['email']) ||
                !is_string($userInfo['email']) ||
                !filter_var(
                    $userInfo['email'],
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Invalid response from Auth0.'
                    );
            }

            $email = strtolower(trim($userInfo['email']));

            $user = User::where('email', $email)->first();

            if (!$user) {

                $name = $userInfo['name']
                    ?? $userInfo['nickname']
                    ?? 'Auth0 User';

                if (
                    !is_string($name) ||
                    strlen($name) > 100
                ) {
                    $name = 'Auth0 User';
                }

                $user = User::create([
                    'name' => trim($name),
                    'email' => $email,
                    'password' => Str::random(64),
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user, true);

            request()
                ->session()
                ->regenerate();

            return redirect()
                ->route('home')
                ->with(
                    'success',
                    'Logged in via Auth0!'
                );

        } catch (\Throwable $e) {

            Log::error(
                'Auth0 Callback Error',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Authentication failed. Please try again.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GOOGLE
    |--------------------------------------------------------------------------
    */

    public function redirect()
    {
        try {

            return Socialite::driver('google')
                ->stateless()
                ->redirect();

        } catch (\Throwable $e) {

            Log::error(
                'Google redirect error',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Unable to connect to Google. Please try again.'
                );
        }
    }

    public function callback()
    {
        try {

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $googleEmail = $googleUser?->getEmail();

            if (
                !$googleUser ||
                !is_string($googleEmail) ||
                !filter_var(
                    $googleEmail,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                return redirect()
                    ->route('login')
                    ->with(
                        'error',
                        'Invalid response from Google.'
                    );
            }

            $email = strtolower(trim($googleEmail));

            $user = User::where('email', $email)->first();

            if (!$user) {

                $name = $googleUser->getName() ?? 'User';

                if (
                    !is_string($name) ||
                    strlen($name) > 100
                ) {
                    $name = 'User';
                }

                $user = User::create([
                    'name' => trim($name),
                    'email' => $email,
                    'password' => Str::random(64),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                ]);

            } else {

                $googleId = $googleUser->getId();

                if (
                    empty($user->google_id) &&
                    is_string($googleId)
                ) {
                    $user->update([
                        'google_id' => $googleId,
                    ]);
                }
            }

            Auth::login($user, true);

            request()
                ->session()
                ->regenerate();

            return redirect()
                ->route('home')
                ->with(
                    'success',
                    'Welcome back, ' . $user->name . '!'
                );

        } catch (\Throwable $e) {

            Log::error(
                'Google callback error',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Authentication failed. Please try again.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Logged out successfully!'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICATION CODE GENERATOR
    |--------------------------------------------------------------------------
    */

    private function generateVerificationCode(): string
    {
        return str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}