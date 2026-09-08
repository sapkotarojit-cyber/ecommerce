@extends('frontend.frontend-layout')

@section('title', 'Shopping Cart - Empireinnovation')

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-[#1a2a6c] mb-6">Shopping Cart</h1>

        @if(session('cart') && count(session('cart')) > 0)
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items Table -->
                <div class="flex-1 bg-white rounded-xl shadow-sm p-6">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b text-sm text-gray-500">
                                <th class="pb-3">Product</th>
                                <th class="pb-3">Price</th>
                                <th class="pb-3">Quantity</th>
                                <th class="pb-3">Subtotal</th>
                                <th class="pb-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php $total = 0; @endphp
                            @foreach(session('cart') as $id => $details)
                                @php 
                                    $subtotal = $details['price'] * $details['quantity'];
                                    $total += $subtotal;
                                @endphp
                                <tr>
                                    <td class="py-4 font-semibold text-[#1a2a6c]">{{ $details['title'] }}</td>
                                    <td class="py-4">Rs. {{ number_format($details['price'], 2) }}</td>
                                    <td class="py-4">
                                        <form action="{{ route('cart.update', $id) }}" method="POST" class="flex items-center">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="quantity" value="{{ $details['quantity'] }}" min="1" class="w-16 px-2 py-1 border rounded text-center text-sm mr-2">
                                            <button type="submit" class="text-xs text-blue-600 hover:underline">Update</button>
                                        </form>
                                    </td>
                                    <td class="py-4 font-bold text-[#1a2a6c]">Rs. {{ number_format($subtotal, 2) }}</td>
                                    <td class="py-4 text-right">
                                        <form action="{{ route('cart.destroy', $id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-6 flex justify-between items-center border-t pt-4">
                        <form action="{{ route('cart.clear') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:underline">Clear Cart</button>
                        </form>
                        <a href="{{ route('products') }}" class="text-sm text-[#1a2a6c] hover:underline">Continue Shopping</a>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="w-full lg:w-80 bg-white rounded-xl shadow-sm p-6 h-fit">
                    <h3 class="font-bold text-[#1a2a6c] mb-4">Order Summary</h3>
                    <div class="flex justify-between mb-2 text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-semibold">Rs. {{ number_format($total, 2) }}</span>
                    </div>
                    <div class="border-t pt-3 mt-3 flex justify-between font-bold text-[#1a2a6c]">
                        <span>Total</span>
                        <span>Rs. {{ number_format($total, 2) }}</span>
                    </div>
                    <a href="{{ route('orders.checkout') }}" class="block w-full text-center mt-6 px-4 py-3 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-all">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        @else
            <div class="text-center py-16 bg-white rounded-xl shadow-sm">
                <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">Your cart is currently empty.</p>
                <a href="{{ route('products') }}" class="inline-block px-6 py-2 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c]">
                    Browse Products
                </a>
            </div>
        @endif
    </div>
</section>
@endsection