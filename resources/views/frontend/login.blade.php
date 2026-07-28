{{-- <x-frontend-layout>
    @section('title', 'Login - Empireinnovation PVT.LTD')

    @section('content')
    <!-- Login Section -->
    <section class="min-h-[70vh] flex items-center justify-center py-12">
        <div class="container-custom">
            <div class="max-w-sm mx-auto">
                <!-- Login Card -->
                <div class="card p-8 md:p-10 shadow-empire-lg text-center">
                    <!-- Logo -->
                    <div class="w-16 h-16 gradient-primary rounded-2xl flex items-center justify-center text-white font-extrabold text-2xl mx-auto shadow-lg mb-4">
                        EI
                    </div>

                    <h1 class="text-2xl font-bold text-primary-500">Welcome Back</h1>
                    <p class="text-gray-500 text-sm mt-1">Sign in to continue</p>

                    <div class="mt-8">
                        <!-- Google Login Button -->
                        <a href="{{ route('redirect') }}"
                           class="group w-full flex items-center justify-center gap-3 px-4 py-3.5 border-2 border-gray-200 rounded-xl hover:border-primary-300 hover:bg-primary-50 transition-all duration-300">
                            <!-- Google Icon -->
                            <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 48 48">
                                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                            </svg>

                            <span class="text-base font-medium text-gray-700 group-hover:text-primary-500 transition-colors">
                                Sign in with Google
                            </span>
                        </a>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="px-3 bg-white text-gray-400">Secure Login</span>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="flex justify-center gap-6 text-xs text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Encrypted
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Fast
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Secure
                        </span>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <p class="text-[10px] text-gray-400">
                            By signing in, you agree to our
                            <a href="#" class="text-primary-500 hover:underline">Terms</a> &amp;
                            <a href="#" class="text-primary-500 hover:underline">Privacy</a>
                        </p>
                    </div>
                </div>

                <!-- Back Link -->
                <div class="text-center mt-4">
                    <a href="{{ route('home') }}" class="text-sm text-gray-400 hover:text-primary-500 transition-colors inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </section>
    @endsection
</x-frontend-layout> --}}


<!-- resources/views/frontend/login.blade.php -->
@extends('frontend.frontend-layout')

@section('title', 'Login - Empireinnovation')

@section('content')
<section class="py-12 md:py-20">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-[#c9a84c]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-circle text-3xl text-[#c9a84c]"></i>
                </div>
                <h2 class="text-2xl font-bold text-[#1a2a6c]">Welcome Back!</h2>
                <p class="text-gray-500 text-sm mt-1">Sign in to your account to continue</p>
            </div>
            
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif
            
            <!-- Google Login -->
            <a href="{{ route('redirect') }}" class="flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all text-gray-700 font-medium">
                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continue with Google
            </a>
            
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">Or continue with email</span>
                </div>
            </div>
            
            <!-- Email Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="you@example.com" required>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="••••••••" required>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded text-[#c9a84c] focus:ring-[#c9a84c]">
                        <span class="ml-2">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-[#c9a84c] hover:text-[#b8963a]">Forgot password?</a>
                </div>
                
                <button type="submit" class="w-full px-4 py-3 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-all">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                </button>
            </form>
            
            <p class="text-center text-sm text-gray-500 mt-6">
                Don't have an account? 
                <a href="{{ route('login') }}" class="text-[#c9a84c] font-semibold hover:text-[#b8963a]">Create one</a>
            </p>
        </div>
    </div>
</section>
@endsection
