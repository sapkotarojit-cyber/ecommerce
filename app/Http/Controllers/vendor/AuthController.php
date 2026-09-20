<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('vendor.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('dokan')->attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dokan/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid vendor credentials provided.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('dokan')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/vendor/login')->with('success', 'Logged out successfully.');
    }
}