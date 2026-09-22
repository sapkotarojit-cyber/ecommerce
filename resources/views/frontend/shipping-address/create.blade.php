@extends('frontend.frontend-layout')

@section('title', 'Add New Address - Empireinnovation')
<title>Add New Address - EmpireInnovation</title>

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <div class="flex items-center space-x-3 mb-6">
                <a href="{{ route('shipping-address.index') }}" class="text-[#1a2a6c] hover:text-[#c9a84c] transition-colors">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <h1 class="text-2xl font-bold text-[#1a2a6c]">Add New Address</h1>
            </div>
            
            <form action="{{ route('shipping-address.store') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Recipient's Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Recipient’s Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Input the real name" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Phone Number -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Please input Phone Number" required>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Region/City/District -->
                <div>
                    <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region/City/District <span class="text-red-500">*</span></label>
                    <input type="text" id="region" name="region" value="{{ old('region') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Please input Region/City/District" required>
                    @error('region')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                    <textarea id="address" name="address" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                              placeholder="House no./building/street/area" required>{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Landmark (Optional) -->
                <div>
                    <label for="landmark" class="block text-sm font-medium text-gray-700 mb-1">Landmark (Optional)</label>
                    <input type="text" id="landmark" name="landmark" value="{{ old('landmark') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent transition-all"
                           placeholder="Add Additional Info">
                    @error('landmark')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address Category</label>
                    <div class="flex items-center space-x-6">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="address_type" value="Home" {{ old('address_type', 'Home') == 'Home' ? 'checked' : '' }} class="text-[#c9a84c] focus:ring-[#c9a84c]">
                            <span class="text-sm text-gray-700">Home</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="address_type" value="Office" {{ old('address_type') == 'Office' ? 'checked' : '' }} class="text-[#c9a84c] focus:ring-[#c9a84c]">
                            <span class="text-sm text-gray-700">Office</span>
                        </label>
                    </div>
                </div>
                
                <!-- Default Toggles -->
                <div class="space-y-3 pt-2 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Default Shipping Address</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_default_shipping" value="1" class="sr-only peer" {{ old('is_default_shipping') ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#c9a84c]"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Default Billing Address</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_default_billing" value="1" class="sr-only peer" {{ old('is_default_billing') ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#c9a84c]"></div>
                        </label>
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-3.5 bg-[#f97316] text-white font-bold rounded-lg hover:bg-[#ea580c] transition-all shadow-md">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection