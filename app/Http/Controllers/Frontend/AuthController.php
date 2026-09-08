<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show login page
     */
    public function login()
    {
        return view('frontend.login');
    }

    /**
     * Handle Email/Password Login
     */
    public function loginSubmit(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'))->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration page
     */
    public function register()
    {
        return view('frontend.register');
    }

    /**
     * Handle Registration
     */
    public function registerSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Account created successfully!');
    }

    /**
     * Redirect to Google for authentication
     */
    public function redirect()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            Log::error('Google redirect error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Unable to connect to Google. Please try again.');
        }
    }

    /**
     * Handle Google callback
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            if (!$googleUser || !$googleUser->getEmail()) {
                return redirect()->route('login')->with('error', 'Invalid response from Google.');
            }

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'User',
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
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

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully!');
    }
}