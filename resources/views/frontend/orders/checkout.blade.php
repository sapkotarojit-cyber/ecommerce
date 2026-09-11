@extends('frontend.frontend-layout')

@section('title', 'Checkout')

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Checkout</h1>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('orders.store') }}" method="POST">
            @csrf
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <!-- Left Section: Shipping & Payment -->
                <div class="w-full lg:flex-1 space-y-6">
                    
                    <!-- Shipping Address Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Shipping Address</h2>
                            <a href="{{ route('shipping-address.create') }}" class="inline-flex items-center text-xs font-semibold uppercase tracking-wider text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1.5 rounded-lg transition-colors">
                                + Add New Address
                            </a>
                        </div>

                        <div>
                            @if($addresses->isEmpty())
                                <p class="text-sm text-gray-500 py-2">No shipping addresses found. Please add an address to proceed.</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($addresses as $address)
                                        <label class="relative flex items-start p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-indigo-500 transition-all bg-gray-50/50 has-[:checked]:bg-indigo-50/30 has-[:checked]:border-indigo-600">
                                            <div class="flex items-center h-5">
                                                <input type="radio" name="shipping_address_id" value="{{ $address->id }}" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ $loop->first ? 'checked' : '' }} required>
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="font-semibold text-gray-900">{{ $address->title }}</span>
                                                <span class="text-gray-500 font-normal">({{ $address->contact_no }})</span>
                                                <p class="text-gray-600 mt-0.5">
                                                    {{ $address->full_address }}
                                                </p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                            @error('shipping_address_id')
                                <p class="text-red-500 text-xs font-medium mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Payment Method Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="pb-4 mb-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Payment Method</h2>
                        </div>
                        <div class="space-y-3">
                            <label class="relative flex items-center p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-indigo-500 transition-all bg-gray-50/50 has-[:checked]:bg-indigo-50/30 has-[:checked]:border-indigo-600">
                                <input type="radio" name="payment_method" value="cod" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" checked>
                                <span class="ml-3 text-sm font-semibold text-gray-900">Cash on Delivery (COD)</span>
                            </label>
                            
                            <label class="relative flex items-center p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-indigo-500 transition-all bg-gray-50/50 has-[:checked]:bg-indigo-50/30 has-[:checked]:border-indigo-600">
                                <input type="radio" name="payment_method" value="esewa" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-semibold text-gray-900">Pay with eSewa (Online Payment)</span>
                            </label>

                            <label class="relative flex items-center p-4 rounded-xl border border-gray-200 cursor-pointer hover:border-indigo-500 transition-all bg-gray-50/50 has-[:checked]:bg-indigo-50/30 has-[:checked]:border-indigo-600">
                                <input type="radio" name="payment_method" value="bank" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                <span class="ml-3 text-sm font-semibold text-gray-900">Direct Bank Transfer</span>
                            </label>
                        </div>
                        @error('payment_method')
                            <p class="text-red-500 text-xs font-medium mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Right Section: Order Summary Sidebar -->
                <div class="w-full lg:w-96 lg:sticky lg:top-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="pb-4 mb-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
                        </div>

                        <div class="space-y-4 mb-6">
                            @foreach($vendorTotal as $dokanId => $vendorGroup)
                                <div class="pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                    <div class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-2">
                                        Store: {{ $vendorGroup['dokan']->name ?? 'Default Store' }}
                                    </div>
                                    <div class="space-y-2 mb-3">
                                        @foreach($vendorGroup['items'] as $item)
                                            @php
                                                $price = $item->varient->price ?? $item->product->price ?? 0;
                                                $discount = $item->varient->discount ?? 0;
                                                $finalPrice = $price - ($price * $discount / 100);
                                            @endphp
                                            <div class="flex justify-between items-start text-sm">
                                                <div>
                                                    <span class="font-medium text-gray-900 block">{{ $item->product->name ?? 'Product' }}</span>
                                                    <span class="text-xs text-gray-500">Qty: {{ $item->qty }} × ${{ number_format($finalPrice, 2) }}</span>
                                                </div>
                                                <span class="font-semibold text-gray-900">${{ number_format($finalPrice * $item->qty, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 font-medium">
                                        <span>Store Subtotal</span>
                                        <span>${{ number_format($vendorGroup['subtotal'], 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-4 border-t border-gray-200 flex justify-between items-center mb-6">
                            <span class="text-base font-bold text-gray-900">Total Amount</span>
                            <span class="text-xl font-extrabold text-gray-900">${{ number_format($grandTotal, 2) }}</span>
                        </div>

                        <button type="submit" class="w-full py-3.5 px-4 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-xl shadow-sm transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed text-sm uppercase tracking-wider" {{ $addresses->isEmpty() ? 'disabled' : '' }}>
                            Place Order
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection