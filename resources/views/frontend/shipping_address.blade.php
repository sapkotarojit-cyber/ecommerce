@extends('components.frontend-layout')

@section('title', 'Edit Shipping Address - Empireinnovation PVT.LTD')

@section('content')
<div class="container-custom py-8 max-w-2xl">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-primary-500 transition-colors">Home</a>
        <span>/</span>
        <a href="{{ route('shipping-addresses.index') }}" class="hover:text-primary-500 transition-colors">Shipping Addresses</a>
        <span>/</span>
        <span class="text-primary-500 font-medium">Edit Address</span>
    </nav>

    <!-- Form Card -->
    <div class="card p-6 md:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center text-primary-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-primary-500">Edit Shipping Address</h1>
                <p class="text-sm text-gray-500">Update the details of your shipping address</p>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('shipping-addresses.update', $address) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Address Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Address Title <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title', $address->title) }}"
                       placeholder="e.g., Home, Office, Parents' House"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition @error('title') border-red-500 @enderror"
                       required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Contact Number -->
            <div>
                <label for="contact_no" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Contact Number <span class="text-red-500">*</span>
                </label>
                <input type="tel"
                       id="contact_no"
                       name="contact_no"
                       value="{{ old('contact_no', $address->contact_no) }}"
                       placeholder="e.g., +977 9841234567"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition @error('contact_no') border-red-500 @enderror"
                       required>
                @error('contact_no')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Full Address -->
            <div>
                <label for="full_address" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Full Address <span class="text-red-500">*</span>
                </label>
                <textarea id="full_address"
                          name="full_address"
                          rows="4"
                          placeholder="Enter your complete address with street, city, state, and zip code"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none transition resize-y @error('full_address') border-red-500 @enderror"
                          required>{{ old('full_address', $address->full_address) }}</textarea>
                @error('full_address')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1">Include apartment/suite number, street, city, state/province, and postal code</p>
            </div>

            <!-- Set as Default -->
            <div class="flex items-center gap-3 p-4 bg-primary-50 rounded-lg">
                <input type="checkbox"
                       id="is_default"
                       name="is_default"
                       value="1"
                       {{ old('is_default', $address->is_default) ? 'checked' : '' }}
                       class="w-4 h-4 text-accent border-gray-300 rounded focus:ring-accent">
                <label for="is_default" class="text-sm font-medium text-gray-700 cursor-pointer">
                    Set as default shipping address
                </label>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="btn-primary flex-1 sm:flex-none min-w-[120px]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Address
                </button>
                <a href="{{ route('shipping-addresses.index') }}"
                   class="btn-secondary flex-1 sm:flex-none min-w-[120px] text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Address Info -->
    @if($address->is_default)
        <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-100">
            <div class="flex items-center gap-2 text-green-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium">This is currently your default shipping address</span>
            </div>
        </div>
    @endif
</div>
@endsection
