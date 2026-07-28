@extends('frontend.frontend-layout')

@section('title', 'Shopping Cart - Empireinnovation')

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl md:text-3xl font-bold text-[#1a2a6c] mb-6">Shopping Cart</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif

        @if($cartItems->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-[#1a2a6c]">Your Cart is Empty</h3>
                <p class="text-gray-500 text-sm mt-1">Start shopping to add items to your cart</p>
                <a href="{{ route('products') }}" class="inline-flex items-center px-6 py-3 bg-[#c9a84c] text-[#1a2a6c] font-semibold rounded-lg hover:bg-[#dbb95c] transition-all mt-4">
                    <i class="fas fa-shopping-bag mr-2"></i> Start Shopping
                </a>
            </div>
        @else
            <!-- Vendor Groups -->
            @foreach($vendorTotal as $vendorId => $vendorData)
                <div class="bg-white rounded-xl shadow-sm mb-6 overflow-hidden">
                    <div class="bg-[#f8f7f4] px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center">
                            <i class="fas fa-store text-[#c9a84c] mr-2"></i>
                            <span class="font-semibold text-[#1a2a6c]">{{ $vendorData['dokan']->company_name ?? 'Vendor' }}</span>
                            <span class="text-sm text-gray-500 ml-2">({{ count($vendorData['items']) }} items)</span>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($vendorData['items'] as $item)
                            <div class="p-4 flex flex-col sm:flex-row items-start sm:items-center gap-4" id="cart-item-{{ $item->id }}">
                                <!-- Product Image -->
                                <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    @php
                                        $images = json_decode($item->varient->images ?? '[]', true);
                                    @endphp
                                    @if(!empty($images) && isset($images[0]))
                                        <img src="{{ asset('storage/' . $images[0]) }}"
                                             alt="{{ $item->product->title }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400 text-2xl"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('product', $item->product_id) }}" class="font-semibold text-[#1a2a6c] hover:text-[#c9a84c] transition-colors">
                                        {{ $item->product->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">{{ $item->varient->title }}</p>

                                    @if($item->varient->discount > 0)
                                        <div class="flex items-center space-x-2 mt-1">
                                            <span class="text-sm text-gray-400 line-through">Rs. {{ number_format($item->varient->price, 2) }}</span>
                                            <span class="text-lg font-bold text-[#1a2a6c]">Rs. {{ number_format($item->final_price, 2) }}</span>
                                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">-{{ $item->varient->discount }}%</span>
                                        </div>
                                    @else
                                        <span class="text-lg font-bold text-[#1a2a6c]">Rs. {{ number_format($item->varient->price, 2) }}</span>
                                    @endif
                                </div>

                                <!-- Quantity & Actions -->
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                        <button class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 transition-colors text-[#1a2a6c] font-bold"
                                                onclick="updateCart({{ $item->id }}, 'decrease')">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" value="{{ $item->qty }}" min="1"
                                               class="w-14 text-center border-0 focus:ring-0 py-1.5 text-sm"
                                               id="qty-{{ $item->id }}"
                                               onchange="updateCart({{ $item->id }}, 'set', this.value)">
                                        <button class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 transition-colors text-[#1a2a6c] font-bold"
                                                onclick="updateCart({{ $item->id }}, 'increase')">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>

                                    <form action="{{ route('cart.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" onclick="return confirm('Remove this item?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Vendor Subtotal -->
                    <div class="px-4 py-3 bg-[#f8f7f4] border-t border-gray-100 flex justify-between">
                        <span class="font-semibold text-[#1a2a6c]">Subtotal:</span>
                        <span class="font-bold text-[#1a2a6c]">Rs. {{ number_format($vendorData['subtotal'], 2) }}</span>
                    </div>
                </div>
            @endforeach

            <!-- Cart Total -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 sticky bottom-0">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <span class="font-semibold text-[#1a2a6c]">Total:</span>
                        <span class="text-2xl font-bold text-[#1a2a6c]">Rs. {{ number_format($total, 2) }}</span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <form action="{{ route('cart.clear') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full sm:w-auto px-6 py-2 border border-red-300 text-red-600 font-semibold rounded-lg hover:bg-red-50 transition-all" onclick="return confirm('Clear entire cart?')">
                                <i class="fas fa-trash mr-2"></i> Clear Cart
                            </button>
                        </form>
                        <a href="{{ route('products') }}" class="w-full sm:w-auto px-6 py-2 border border-gray-300 text-gray-600 font-semibold rounded-lg hover:bg-gray-50 transition-all text-center">
                            <i class="fas fa-shopping-bag mr-2"></i> Continue Shopping
                        </a>
                        <a href="{{ route('orders.checkout') }}" class="w-full sm:w-auto px-8 py-2 bg-[#c9a84c] text-[#1a2a6c] font-semibold rounded-lg hover:bg-[#dbb95c] transition-all text-center">
                            <i class="fas fa-credit-card mr-2"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
function updateCart(id, action, value) {
    let qty = document.getElementById('qty-' + id).value;

    if (action === 'increase') {
        qty = parseInt(qty) + 1;
    } else if (action === 'decrease') {
        qty = parseInt(qty) - 1;
        if (qty < 1) qty = 1;
    } else if (action === 'set') {
        qty = parseInt(value);
        if (qty < 1) qty = 1;
    }

    // Update the input
    document.getElementById('qty-' + id).value = qty;

    // Send update via AJAX
    fetch('{{ url("cart") }}/' + id, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qty: qty })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush
@endsection
