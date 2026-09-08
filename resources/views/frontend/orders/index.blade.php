@extends('frontend.frontend-layout')

@section('title', 'My Orders - Empireinnovation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-[#0f1a3a] mb-6">My Orders</h1>

    @if($orders->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @foreach($orders as $order)
                        <tr>
                            <td class="px-6 py-4 font-semibold text-[#0f1a3a]">#{{ $order->id }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($order->status ?? 'Pending') }}
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
            <p class="text-gray-500">You haven't placed any orders yet.</p>
            <a href="{{ route('products') }}" class="mt-4 inline-block px-6 py-2 bg-[#0f1a3a] text-white rounded-lg hover:bg-[#c9a84c] hover:text-[#0f1a3a] transition-all">Start Shopping</a>
        </div>
    @endif
</div>
@endsection