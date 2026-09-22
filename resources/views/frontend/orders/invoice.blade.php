@extends('frontend.frontend-layout')

@section('title', 'Invoice #' . $order->id . ' - Empireinnovation')
<title>Order - EmpireInnovation</title>

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Action Buttons (Print / Back) -->
    <div class="flex justify-between items-center mb-6 print:hidden">
        <a href="{{ route('orders.show', $order->id) }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-[#0f1a3a]">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Order Details
        </a>
        <button onclick="window.print()" class="bg-[#0f1a3a] hover:bg-[#c9a84c] hover:text-[#0f1a3a] text-white px-4 py-2 rounded-lg text-sm font-medium transition-all">
            <i class="fa-solid fa-print mr-2"></i> Print Invoice
        </button>
    </div>

    <!-- Invoice Card Container -->
    <div class="bg-white rounded-lg shadow-lg p-8 print:shadow-none print:p-0">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between border-b pb-6 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#0f1a3a]">INVOICE</h1>
                <p class="text-sm text-gray-500 mt-1">Tracking: {{ $order->tracking_number }}</p>
            </div>
            <div class="mt-4 md:mt-0 text-left md:text-right">
                <h2 class="text-lg font-bold text-[#0f1a3a]">Empireinnovation</h2>
                <p class="text-sm text-gray-500">Official Order Invoice</p>
                <p class="text-sm text-gray-500">Date: {{ $order->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <!-- Vendor & Customer Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 text-sm">
            <div>
                <h3 class="font-bold text-gray-700 uppercase tracking-wider mb-2">Invoice To:</h3>
                <p class="font-semibold text-[#0f1a3a]">
                    {{ $order->shipping_address->title ?? auth()->user()->name ?? 'Valued Customer' }}
                </p>
                <p class="text-gray-600">
                    {{ $order->shipping_address->full_address ?? 'Address not provided' }}
                </p>
                <p class="text-gray-600 mt-1">
                    Phone: {{ $order->shipping_address->contact_no ?? 'N/A' }}
                </p>
            </div>
            <div class="md:text-right">
                <h3 class="font-bold text-gray-700 uppercase tracking-wider mb-2">Order Info:</h3>
                <p class="text-gray-600"><span class="font-medium">Vendor (Dokan):</span> {{ $order->dokan->name ?? 'Main Store' }}</p>
                <p class="text-gray-600"><span class="font-medium">Payment Method:</span> {{ ucfirst($order->payment_method) }}</p>
                <p class="text-gray-600"><span class="font-medium">Payment Status:</span> 
                    <span class="font-semibold {{ $order->payment_status == 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </p>
                <p class="text-gray-600"><span class="font-medium">Order Status:</span> {{ ucfirst($order->order_status) }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mb-8">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Item Description</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-right">Price</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    @foreach($order->order_items as $item)
                        <tr>
                            <td class="px-4 py-4 font-medium text-[#0f1a3a]">
                                {{ $item->product->name ?? 'Product' }}
                                @if($item->varient)
                                    <span class="block text-xs text-gray-500 font-normal">Variant: {{ $item->varient->title ?? $item->varient->name ?? 'Standard' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center text-gray-600">{{ $item->qty }}</td>
                            <td class="px-4 py-4 text-right text-gray-600">${{ number_format($item->amount / max($item->qty, 1), 2) }}</td>
                            <td class="px-4 py-4 text-right font-bold text-[#0f1a3a]">${{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total Calculation -->
        <div class="flex justify-end border-t pt-4">
            <div class="w-full md:w-72 space-y-2 text-sm">
                <div class="flex justify-between font-bold text-base text-[#0f1a3a] border-t pt-2">
                    <span>Grand Total:</span>
                    <span>${{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="mt-12 border-t pt-6 text-center text-xs text-gray-400">
            <p>Thank you for shopping with Empireinnovation! If you have any questions, please contact support.</p>
        </div>
    </div>
</div>
@endsection