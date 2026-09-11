@extends('frontend.frontend-layout')

@section('title', 'Shopping Cart - Empireinnovation')

@section('content')
<section class="py-8 md:py-12 bg-gray-50 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex justify-between items-center">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-700 font-bold">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex justify-between items-center">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-700 font-bold">&times;</button>
            </div>
        @endif

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-[#1a2a6c]">Shopping Cart</h1>
            @if(count($cartItems) > 0)
                <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear your cart?');">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800 transition-colors">
                        <i class="fas fa-trash-alt mr-1"></i> Clear Cart
                    </button>
                </form>
            @endif
        </div>

        @if(count($cartItems) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left: Vendor Grouped Items -->
                <div class="lg:col-span-2 space-y-6">
                    @foreach($vendorTotal as $vendorId => $group)
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
                            <!-- Vendor Header -->
                            <div class="bg-gray-100 px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                                <span class="font-bold text-[#1a2a6c] flex items-center gap-2">
                                    <i class="fas fa-store text-[#c9a84c]"></i>
                                    {{ $group['dokan']->company_name ?? 'Vendor Store' }}
                                </span>
                                <span class="text-xs text-gray-500 font-medium">
                                    Subtotal: <strong class="text-gray-800">Rs. {{ number_format($group['subtotal'], 2) }}</strong>
                                </span>
                            </div>

                            <!-- Cart Items Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b text-xs text-gray-400 uppercase tracking-wider bg-gray-50/50">
                                            <th class="py-3 px-6">Product</th>
                                            <th class="py-3 px-4">Price</th>
                                            <th class="py-3 px-4">Quantity</th>
                                            <th class="py-3 px-4">Subtotal</th>
                                            <th class="py-3 px-6 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-sm">
                                        @foreach($group['items'] as $item)
                                            @php
                                                $price = $item->varient->price;
                                                $discount = $item->varient->discount ?? 0;
                                                $finalPrice = $price - ($price * $discount / 100);
                                                $itemTotal = $finalPrice * $item->qty;

                                                // Parse Image
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
                                            @endphp
                                            <tr>
                                                <td class="py-4 px-6 font-medium text-gray-800">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center border">
                                                            @if($mainImage)
                                                                <img src="{{ asset('storage/' . $mainImage) }}" alt="{{ $item->product->title }}" class="w-full h-full object-cover">
                                                            @else
                                                                <i class="fas fa-image text-gray-400"></i>
                                                            @endif
                                                        </div>
                                                        <a href="{{ route('product', $item->product->id) }}" class="hover:text-[#c9a84c] line-clamp-2">
                                                            {{ $item->product->title }}
                                                        </a>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-4 whitespace-nowrap">
                                                    Rs. {{ number_format($finalPrice, 2) }}
                                                    @if($discount > 0)
                                                        <span class="block text-xs text-gray-400 line-through">Rs. {{ number_format($price, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td class="py-4 px-4 whitespace-nowrap">
                                                    <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center gap-2">
                                                        @csrf
                                                        @method('POST')
                                                        <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="{{ $item->varient->qty }}" class="w-16 px-2 py-1 border border-gray-200 rounded text-center text-sm focus:ring-1 focus:ring-[#c9a84c]">
                                                        <button type="submit" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded transition-colors">Save</button>
                                                    </form>
                                                </td>
                                                <td class="py-4 px-4 font-bold text-[#1a2a6c] whitespace-nowrap">
                                                    Rs. {{ number_format($itemTotal, 2) }}
                                                </td>
                                                <td class="py-4 px-6 text-right whitespace-nowrap">
                                                    <form action="{{ route('cart.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Remove this item?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex justify-between items-center pt-2">
                        <a href="{{ route('products') }}" class="text-sm font-semibold text-[#1a2a6c] hover:underline flex items-center gap-2">
                            <i class="fas fa-arrow-left text-xs"></i>
                            <span>Continue Shopping</span>
                        </a>
                    </div>
                </div>

                <!-- Right: Order Summary Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 sticky top-6">
                        <h3 class="font-bold text-[#1a2a6c] mb-4 pb-3 border-b border-gray-100 text-lg">Order Summary</h3>

                        <div class="space-y-3 text-sm mb-4">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-semibold text-gray-800">Rs. {{ number_format($total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Estimated Shipping</span>
                                <span class="text-xs text-gray-400 italic">Calculated at checkout</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 mt-4 flex justify-between items-center">
                            <span class="font-bold text-gray-800 text-base">Grand Total</span>
                            <span class="font-extrabold text-2xl text-[#1a2a6c]">Rs. {{ number_format($total, 2) }}</span>
                        </div>

                        <a href="{{ route('orders.checkout') }}" class="mt-6 w-full py-3.5 bg-[#1a2a6c] text-white font-bold rounded-lg hover:bg-[#2a3a7c] transition-all shadow-md flex items-center justify-center gap-2">
                            <span>Proceed to Checkout</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>

            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 bg-white rounded-xl shadow-sm max-w-lg mx-auto border border-gray-100">
                <div class="w-20 h-20 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shopping-cart text-3xl"></i>
                </div>
                <h2 class="text-xl font-bold text-[#1a2a6c] mb-2">Your cart is currently empty</h2>
                <p class="text-gray-500 text-sm mb-6">Explore our store and add items to your cart.</p>
                <a href="{{ route('products') }}" class="inline-flex items-center justify-center px-6 py-3 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-colors gap-2">
                    <i class="fas fa-store"></i>
                    <span>Browse Products</span>
                </a>
            </div>
        @endif

    </div>
</section>
@endsection