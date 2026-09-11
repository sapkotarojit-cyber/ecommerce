@extends('frontend.frontend-layout')

@section('title', 'My Orders - Empireinnovation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-[#0f1a3a]">My Orders</h1>
        
        <!-- Search Form -->
        <form action="{{ route('orders.index') }}" method="GET" class="flex w-full md:w-auto gap-2">
            <div class="relative flex-1 md:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by tracking number or ID..." class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#0f1a3a] focus:border-transparent">
                @if(request('search'))
                    <a href="{{ route('orders.index') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
            <button type="submit" class="bg-[#0f1a3a] hover:bg-[#c9a84c] hover:text-[#0f1a3a] text-white px-4 py-2 rounded-lg text-sm font-medium transition-all">
                <i class="fa-solid fa-search mr-1"></i> Search
            </button>
        </form>
    </div>

    @if($orders->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product Name / Tracking</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @foreach($orders as $order)
                        <tr>
                            <td class="px-6 py-4 font-semibold text-[#0f1a3a]">
                                @php
                                    $firstItem = $order->order_items->first();
                                @endphp
                                {{ $firstItem->product->name ?? 'Order #' . $order->id }}
                                @if($order->order_items->count() > 1)
                                    <span class="text-xs text-gray-500 font-normal block">+ {{ $order->order_items->count() - 1 }} more item(s)</span>
                                @endif
                                <span class="text-xs text-gray-400 font-normal block">Tracking: {{ $order->tracking_number }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($order->order_status ?? $order->status ?? 'Pending') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold">${{ number_format($order->total_amount ?? $order->total, 2) }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-[#c9a84c] hover:underline font-medium">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @else
        <div class="bg-white p-8 text-center rounded-lg shadow">
            <p class="text-gray-500">
                @if(request('search'))
                    No orders found matching "{{ request('search') }}".
                @else
                    You haven't placed any orders yet.
                @endif
            </p>
            @if(request('search'))
                <a href="{{ route('orders.index') }}" class="mt-4 inline-block px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all">Clear Search</a>
            @else
                <a href="{{ route('products') }}" class="mt-4 inline-block px-6 py-2 bg-[#0f1a3a] text-white rounded-lg hover:bg-[#c9a84c] hover:text-[#0f1a3a] transition-all">Start Shopping</a>
            @endif
        </div>
    @endif
</div>
@endsection