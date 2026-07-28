<!-- resources/views/frontend/shipping-address/create.blade.php -->
@extends('frontend.frontend-layout')

@section('title', 'Add Shipping Address - Empireinnovation')

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <div class="flex items-center space-x-3 mb-6">
                <a href="{{ route('shipping-address.index') }}" class="text-[#1a2a6c] hover:text-[#c9a84c] transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h1 class="text-2xl font-bold text-[#1a2a6c]">Add Shipping Address</h1>
            </div>
            
            <form action="{{ route('shipping-address.store') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Address Title <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Home, Office, etc." required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="contact_no" class="block text-sm font-medium text-gray-700 mb-1">Contact Number <span class="text-red-500">*</span></label>
                    <input type="text" id="contact_no" name="contact_no" value="{{ old('contact_no') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="+977-98XXXXXXXX" required>
                    @error('contact_no')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="full_address" class="block text-sm font-medium text-gray-700 mb-1">Full Address <span class="text-red-500">*</span></label>
                    <textarea id="full_address" name="full_address" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                              placeholder="Street, City, State, Postal Code" required>{{ old('full_address') }}</textarea>
                    @error('full_address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="is_default" name="is_default" value="1" 
                           class="rounded text-[#c9a84c] focus:ring-[#c9a84c]">
                    <label for="is_default" class="text-sm text-gray-600">Set as default shipping address</label>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    <button type="submit" class="flex-1 px-6 py-3 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-all">
                        <i class="fas fa-save mr-2"></i> Save Address
                    </button>
                    <a href="{{ route('shipping-address.index') }}" class="flex-1 px-6 py-3 border border-gray-300 text-gray-600 font-semibold rounded-lg hover:bg-gray-50 transition-all text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection