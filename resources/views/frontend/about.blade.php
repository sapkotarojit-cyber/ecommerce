@extends('frontend.frontend-layout')

@section('title', 'About Us - Empireinnovation PVT. LTD')

@section('content')
<!-- Hero Header -->
<div class="bg-gradient-to-r from-[#0f1a3a] to-[#1e293b] text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">About Empire innovation</h1>
        <p class="text-lg text-[#c9a84c] max-w-2xl mx-auto font-medium">
            Empowering medical sellers, surgical suppliers, and healthcare institutions through a unified e-commerce platform.
        </p>
    </div>
</div>

<!-- Main Section -->
<div class="container mx-auto px-4 py-12 max-w-6xl">
    <!-- Story & Mission -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-16">
        <div>
            <h2 class="text-3xl font-bold text-[#0f1a3a] mb-4">Who We Are</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                <strong>Empireinnovation PVT. LTD</strong> is a premier multi-seller marketplace tailored specifically for medical, surgical, and healthcare equipment. We connect verified vendors directly with hospitals, clinics, and individual healthcare professionals.
            </p>
            <p class="text-gray-600 leading-relaxed">
                By bridging the gap between trusted manufacturers and healthcare providers, we simplify procurement, streamline delivery, and maintain strict quality standards.
            </p>
        </div>
        <div class="bg-gray-50 border border-gray-100 p-8 rounded-2xl shadow-sm text-center">
            <div class="w-16 h-16 bg-[#c9a84c]/20 text-[#c9a84c] rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-[#0f1a3a] mb-2">Our Core Mission</h3>
            <p class="text-sm text-gray-500">
                To modernize medical procurement across Nepal by offering high-grade equipment, transparent pricing, and fast delivery for healthcare professionals.
            </p>
        </div>
    </div>

    <!-- Highlights Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm text-center">
            <div class="w-12 h-12 bg-[#0f1a3a]/10 text-[#0f1a3a] rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-shield text-xl"></i>
            </div>
            <h3 class="font-bold text-[#0f1a3a] text-lg mb-2">Verified Sellers</h3>
            <p class="text-sm text-gray-500">Every seller on our platform undergoes strict business verification and document checks.</p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm text-center">
            <div class="w-12 h-12 bg-[#0f1a3a]/10 text-[#0f1a3a] rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-boxes text-xl"></i>
            </div>
            <h3 class="font-bold text-[#0f1a3a] text-lg mb-2">Wide Inventory</h3>
            <p class="text-sm text-gray-500">From everyday disposable surgical gear to advanced medical diagnostic machinery.</p>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm text-center">
            <div class="w-12 h-12 bg-[#0f1a3a]/10 text-[#0f1a3a] rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-[#c9a84c] fa-truck-fast text-xl"></i>
            </div>
            <h3 class="font-bold text-[#0f1a3a] text-lg mb-2">Reliable Logistics</h3>
            <p class="text-sm text-gray-500">Fast nationwide tracking and delivery to ensure critical medical supplies arrive on time.</p>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-[#0f1a3a] text-white rounded-2xl p-8 md:p-12 text-center">
        <h2 class="text-2xl md:text-3xl font-bold mb-3">Are you a Medical Equipment Supplier?</h2>
        <p class="text-gray-300 text-sm max-w-xl mx-auto mb-6">
            Grow your business by reaching thousands of healthcare buyers across Nepal. Register your vendor shop today.
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('dokan_registration') }}" class="px-6 py-3 bg-[#c9a84c] text-[#0f1a3a] font-bold rounded-lg hover:bg-white transition-all">
                Become a Seller
            </a>
            <a href="{{ route('support') }}" class="px-6 py-3 border border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-all">
                Contact Support
            </a>
        </div>
    </div>
</div>
@endsection