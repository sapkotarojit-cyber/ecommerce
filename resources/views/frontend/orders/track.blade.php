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
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h2 class="text-xl font-bold mb-4">Order Details #{{ $order->id }}</h2>
            <p class="text-sm text-gray-600 mb-2"><strong>Tracking Number:</strong> {{ $order->tracking_number ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600 mb-2"><strong>Status:</strong> <span class="capitalize font-semibold">{{ $order->status ?? 'Processing' }}</span></p>
        </div>
    @endif
</div>
@endsection