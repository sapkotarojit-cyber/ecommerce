@extends('frontend.frontend-layout')

@section('title', 'Verify Email - Empireinnovation')
<title>Verify Email - EmpireInnovation</title>

@section('content')
<section class="py-12 md:py-20">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <div class="w-16 h-16 bg-[#c9a84c]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-envelope-open-text text-3xl text-[#c9a84c]"></i>
            </div>
            <h2 class="text-2xl font-bold text-[#1a2a6c] mb-2">Enter Verification Code</h2>
            <p class="text-gray-500 text-sm mb-6">We have sent a 6-digit verification code to your email address.</p>

            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('verify.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <input type="text" name="code" 
                           class="w-full text-center text-2xl tracking-widest px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all @error('code') border-red-500 @enderror" 
                           placeholder="123456" maxlength="6" required autofocus>
                    @error('code')
                        <p class="text-red-500 text-xs mt-1 text-left">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full px-4 py-3 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-all">
                    Verify Email
                </button>
            </form>

            <form action="{{ route('verify.resend') }}" method="POST" class="mt-4">
                @csrf
                <button type="submit" class="text-sm text-[#c9a84c] font-semibold hover:text-[#b8963a] bg-transparent border-0 cursor-pointer">
                    Didn't receive a code? Resend Code
                </button>
            </form>
        </div>
    </div>
</section>
@endsection