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
                    {{ $order->status ?? 'Processing' }}
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
                        <div class="py-3 flex justify-between items-center text-sm gap-4">
                            <div class="flex items-center gap-3">
                                @if(isset($item->product->image))
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name ?? 'Product' }}" class="w-12 h-12 object-cover rounded-lg border">
                                @endif
                                <div>
                                    <span class="font-medium text-gray-900 block">{{ $item->product->name ?? 'Product Name Unavailable' }}</span>
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
                <span class="text-lg">${{ number_format($order->total_amount ?? $order->grand_total ?? 0, 2) }}</span>
            </div>
        </div>
    @endif
</div>
@endsection