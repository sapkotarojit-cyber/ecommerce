@extends('frontend.frontend-layout')

@section('title', 'Order Details - ' . ($order->tracking_number ?? $order->id))

@section('content')
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Top Navigation / Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <a href="{{ route('orders.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 mb-2">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Orders
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                    Order #{{ $order->tracking_number ?? $order->id }}
                </h1>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                    <i class="fa-solid fa-print mr-2 text-gray-400"></i> Print Invoice
                </a>

                @if($order->order_status === 'pending')
                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-50 border border-red-200 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-100 transition-colors">
                            Cancel Order
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <!-- Order Overview Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Order Date</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $order->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Payment Method</span>
                    <span class="text-sm font-semibold text-gray-900 uppercase">{{ $order->payment_method }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Payment Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Order Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold 
                        @if($order->order_status === 'completed') bg-emerald-100 text-emerald-800
                        @elseif($order->order_status === 'cancelled') bg-red-100 text-red-800
                        @else bg-blue-100 text-blue-800 @endif">
                        {{ ucfirst($order->order_status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <!-- Shipping Information -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-1">
                <h2 class="text-base font-semibold text-gray-900 pb-3 mb-3 border-b border-gray-100">Shipping Address</h2>
                @if($order->shipping_address)
                    <div class="text-sm text-gray-600 space-y-1">
                        <p class="font-semibold text-gray-900">{{ $order->shipping_address->name ?? $order->shipping_address->title ?? 'N/A' }} <span class="text-xs font-normal text-gray-500">({{ $order->shipping_address->address_type ?? '' }})</span></p>
                        <p>{{ $order->shipping_address->address ?? $order->shipping_address->full_address ?? '' }}</p>
                        <p>{{ $order->shipping_address->region ?? '' }}</p>
                        <p class="text-gray-500">Phone: {{ $order->shipping_address->phone ?? $order->shipping_address->contact_no ?? '' }}</p>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No shipping address attached.</p>
                @endif
            </div>

            <!-- Store / Vendor Info -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
                <h2 class="text-base font-semibold text-gray-900 pb-3 mb-3 border-b border-gray-100">Store Information</h2>
                <div class="text-sm text-gray-600 space-y-1">
                    <p class="font-semibold text-gray-900">Store Name: {{ $order->dokan->company_name ?? $order->dokan->name ?? 'Default Marketplace Store' }}</p>
                    @if(isset($order->dokan->email))
                        <p>Email: {{ $order->dokan->email }}</p>
                    @endif
                    @if(isset($order->dokan->phone))
                        <p>Phone: {{ $order->dokan->phone }}</p>
                    @endif
                </div>
            </div>

        </div>

        <!-- Ordered Items -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Items in this Order</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/70 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-6">Product</th>
                            <th class="py-3 px-6">Variant</th>
                            <th class="py-3 px-6 text-center">Qty</th>
                            <th class="py-3 px-6 text-right">Price</th>
                            <th class="py-3 px-6 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($order->order_items as $item)
                            @php
                                // Parse Variant Image safely
                                $rawImages = $item->varient->images ?? [];
                                if (is_string($rawImages)) {
                                    $decoded = json_decode($rawImages, true);
                                    $images = is_array($decoded) ? $decoded : [$rawImages];
                                } else {
                                    $images = (array) $rawImages;
                                }
                                $images = array_map(function($img) {
                                    return trim(str_replace(['\\', '"', '[', ']'], '', $img));
                                }, array_filter($images));
                                $mainImage = $images[0] ?? null;

                                $unitPrice = $item->amount / max($item->qty, 1);
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        @if($mainImage)
                                            <img src="{{ asset('storage/' . $mainImage) }}" alt="Product" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                                <i class="fa-solid fa-box text-xs"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-medium text-gray-900 block">{{ $item->product->title ?? 'Product Name Unavailable' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-gray-600">
                                    {{ $item->varient->title ?? 'Default' }}
                                </td>
                                <td class="py-4 px-6 text-center text-gray-600 font-medium">
                                    {{ $item->qty }}
                                </td>
                                <td class="py-4 px-6 text-right text-gray-600">
                                    Rs. {{ number_format($unitPrice, 2) }}
                                </td>
                                <td class="py-4 px-6 text-right font-semibold text-gray-900">
                                    Rs. {{ number_format($item->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Order Total Footer -->
            <div class="p-6 bg-gray-50/50 border-t border-gray-100 flex justify-between items-center">
                <span class="text-base font-semibold text-gray-900">Grand Total</span>
                <span class="text-xl font-bold text-indigo-600">Rs. {{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

    </div>
</div>
@endsection