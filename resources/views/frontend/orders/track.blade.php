@extends('frontend.frontend-layout')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-2xl font-bold text-[#0f1a3a] mb-6">Track Your Order</h1>

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form action="{{ route('orders.track.submit') }}" method="POST" class="flex flex-col sm:flex-row gap-4">
            @csrf
            <input 
                type="text" 
                name="tracking_number" 
                value="{{ old('tracking_number') }}"
                placeholder="Enter Tracking Number or Order ID" 
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#0f1a3a]"
                required
            >
            <button type="submit" class="bg-[#0f1a3a] text-white px-6 py-2 rounded-lg hover:bg-[#c9a84c] hover:text-[#0f1a3a] transition-colors font-medium">
                Track Order
            </button>
        </form>
    </div>

    @if (isset($order))
        <div class="bg-white p-6 rounded-lg shadow-md border space-y-6">
            <div class="flex justify-between items-center border-b pb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Order Details #{{ $order->id }}</h2>
                    <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-indigo-50 text-indigo-600 capitalize">
                    {{ $order->order_status ?? $order->status ?? 'Processing' }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-lg">
                <div>
                    <p class="text-gray-500 font-medium">Tracking Number</p>
                    <p class="text-gray-900 font-semibold">{{ $order->tracking_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Payment Method</p>
                    <p class="text-gray-900 font-semibold uppercase">{{ $order->payment_method ?? 'COD' }}</p>
                </div>
            </div>

            <!-- Ordered Items -->
            <div>
                <h3 class="text-base font-semibold text-gray-900 mb-3">Items Ordered</h3>
                <div class="divide-y divide-gray-100 border-t border-b border-gray-100">
                @foreach($order->order_items ?? [] as $item)
                    @php
                        // 1. Safely resolve product using optional chaining (?->)
                        $product = $item->product ?? $item->varient?->product ?? null;

                        // 2. Resolve Product Name
                        $productName = $product?->name 
                            ?? $item->varient?->title 
                            ?? 'Product Name Unavailable';

                        // 3. Extract Variant Image (if stored as an array)
                        $variantImages = $item->varient?->images;
                        $variantImage = is_array($variantImages) ? ($variantImages[0] ?? null) : $variantImages;

                        // 4. Fallback chain for the image path
                        $imagePath = $product?->featured_image 
                            ?? $product?->image 
                            ?? $variantImage 
                            ?? null;
                    @endphp

                    <div class="py-3 flex justify-between items-center text-sm gap-4">
                        <div class="flex items-center gap-3">
                            @if($imagePath)
                                <img src="{{ asset('storage/' . $imagePath) }}" 
                                    alt="{{ $productName }}" 
                                    class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                            @else
                                <div class="w-12 h-12 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif

                            <div>
                                <span class="font-medium text-gray-900 block">{{ $productName }}</span>
                                <span class="text-xs text-gray-500">Qty: {{ $item->qty }}</span>
                            </div>
                        </div>
                        <span class="font-semibold text-gray-900">${{ number_format($item->amount, 2) }}</span>
                    </div>
                @endforeach
                </div>
            </div>

            <!-- Total Amount -->
            <div class="flex justify-between items-center pt-2 font-bold text-base text-gray-900">
                <span>Total Amount</span>
                <span class="text-lg">${{ number_format($order->total_amount ?? 0, 2) }}</span>
            </div>
        </div>
    @endif
</div>
@endsection