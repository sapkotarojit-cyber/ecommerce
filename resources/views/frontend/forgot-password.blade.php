@extends('frontend.frontend-layout')

@section('title', 'Forgot Password - Empireinnovation')
<title>Forgot Password - EmpireInnovation</title>

@section('content')
<section class="py-12 md:py-20">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-[#c9a84c]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-[#c9a84c] fa-key text-3xl text-[#c9a84c]"></i>
                </div>
                <h2 class="text-2xl font-bold text-[#1a2a6c]">Forgot Password?</h2>
                <p class="text-gray-500 text-sm mt-1">Enter your email and we'll send you a password reset link.</p>
            </div>

            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm text-center">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="you@example.com" required autofocus>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full px-4 py-3 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-all">
                    Send Reset Link
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Remembered your password? 
                <a href="{{ route('login') }}" class="text-[#c9a84c] font-semibold hover:text-[#b8963a]">Back to Login</a>
            </p>
        </div>
    </div>
</section>
@endsection