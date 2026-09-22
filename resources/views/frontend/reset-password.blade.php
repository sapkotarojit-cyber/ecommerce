@extends('frontend.frontend-layout')

@section('title', 'Reset Password - Empireinnovation')
<title>Reset Password - EmpireInnovation</title>

@section('content')
<section class="py-12 md:py-20">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-[#c9a84c]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-3xl text-[#c9a84c]"></i>
                </div>
                <h2 class="text-2xl font-bold text-[#1a2a6c]">Reset Password</h2>
                <p class="text-gray-500 text-sm mt-1">Enter your new password below.</p>
            </div>

            @if (session('error'))
                <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="you@example.com" required readonly>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" id="password" name="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="••••••••" required>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" class="w-full px-4 py-3 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-all">
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</section>
@endsection