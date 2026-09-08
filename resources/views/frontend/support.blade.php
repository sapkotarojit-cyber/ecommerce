@extends('frontend.frontend-layout')

@section('title', 'Customer Support - Empireinnovation')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-[#0f1a3a]">How can we help you?</h1>
        <p class="text-gray-500 mt-2">Get in touch with the Empireinnovation support team.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <!-- Email Support Card -->
        <div class="bg-white p-6 rounded-xl shadow-sm text-center border border-gray-100 flex flex-col items-center justify-between">
            <div>
                <div class="w-12 h-12 bg-[#c9a84c]/10 text-[#c9a84c] rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-envelope text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800">Email Us</h3>
                <p class="text-sm text-gray-500 mt-1 break-all">empireinnovation2025@gmail.com</p>
            </div>
            <a href="mailto:empireinnovation2025@gmail.com" class="mt-4 inline-block text-sm text-[#c9a84c] font-medium hover:underline">
                Send Email &rarr;
            </a>
        </div>

        <!-- WhatsApp Support Card -->
        <div class="bg-white p-6 rounded-xl shadow-sm text-center border border-gray-100 flex flex-col items-center justify-between">
            <div>
                <div class="w-12 h-12 bg-[#25D366]/10 text-[#25D366] rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fab fa-whatsapp text-2xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800">WhatsApp</h3>
                <p class="text-sm text-gray-500 mt-1">Instant Chat Support</p>
            </div>
            <a href="https://wa.me/1234567890?text=Hello%20Empireinnovation%20Support" target="_blank" class="mt-4 inline-block text-sm text-[#25D366] font-medium hover:underline">
                Chat on WhatsApp &rarr;
            </a>
        </div>

        <!-- Order Inquiry Card -->
        <div class="bg-white p-6 rounded-xl shadow-sm text-center border border-gray-100 flex flex-col items-center justify-between">
            <div>
                <div class="w-12 h-12 bg-[#c9a84c]/10 text-[#c9a84c] rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-truck text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800">Order Inquiry</h3>
                <p class="text-sm text-gray-500 mt-1">Track live status of your purchases</p>
            </div>
            <a href="{{ route('orders.track') }}" class="mt-4 inline-block text-sm text-[#c9a84c] font-medium hover:underline">
                Track an Order &rarr;
            </a>
        </div>
    </div>
</div>
@endsection