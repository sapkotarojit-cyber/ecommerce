@extends('frontend.frontend-layout')

@section('title', 'Shopping Cart - Empireinnovation')
 <title>Cart - EmpireInnovation</title>

@section('content')
<section class="py-8 md:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#1a2a6c]">My Cart</h1>
            @if(!$cartItems->isEmpty())
                <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Clear entire cart?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 text-sm hover:underline flex items-center">
                        <i class="fas fa-trash-alt mr-1"></i> Clear Cart
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($cartItems->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3"></i>
                <h3 class="text-base font-semibold text-[#1a2a6c]">Your Cart is Empty</h3>
                <a href="{{ route('products') }}" class="inline-block mt-4 px-6 py-2 bg-[#c9a84c] text-[#1a2a6c] font-semibold rounded-lg text-sm">
                    Start Shopping
                </a>
            </div>
        @else
            <form action="{{ route('orders.cart.checkout.selected') }}" method="POST" id="cart-form">
                @csrf

                <!-- Vendor Groups -->
                @foreach($vendorTotal as $vendorId => $vendorData)
                    <div class="bg-white rounded-xl shadow-sm mb-4 overflow-hidden border border-gray-100">
                        <!-- Vendor Header with Checkbox -->
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" class="vendor-checkbox rounded border-gray-300 text-[#1a2a6c] focus:ring-[#1a2a6c] h-4 w-4" data-vendor="{{ $vendorId }}">
                                <span class="font-semibold text-sm text-[#1a2a6c] flex items-center">
                                    <i class="fas fa-store text-[#c9a84c] mr-2"></i> {{ $vendorData['dokan']->company_name ?? 'Vendor' }}
                                </span>
                            </label>
                        </div>

                        <!-- Vendor Items -->
                        <div class="divide-y divide-gray-100">
                            @foreach($vendorData['items'] as $item)
                                @php
                                    $price = $item->varient->price ?? 0;
                                    $discount = $item->varient->discount ?? 0;
                                    $finalPrice = $price - ($price * $discount / 100);
                                    $images = is_string($item->varient->images) ? json_decode($item->varient->images, true) : ($item->varient->images ?? []);                                @endphp
                                <div class="p-4 flex items-center gap-4">
                                    <!-- Item Checkbox -->
                                    <div class="flex items-center">
                                        <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" 
                                               class="cart-checkbox vendor-item-{{ $vendorId }} rounded border-gray-300 text-[#1a2a6c] focus:ring-[#1a2a6c] h-4 w-4"
                                               data-price="{{ $finalPrice }}" data-qty="{{ $item->qty }}">
                                    </div>

                                    <!-- Product Image -->
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 border border-gray-100">
                                        @if(!empty($images) && isset($images[0]))
                                            <img src="{{ asset('storage/' . $images[0]) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="fas fa-image text-gray-300"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Details -->
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('product', $item->product_id) }}" class="font-medium text-sm text-[#1a2a6c] hover:underline truncate block">
                                            {{ $item->product->title ?? 'Product' }}
                                        </a>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $item->varient->title ?? '' }}</p>
                                        <p class="text-sm font-bold text-[#1a2a6c] mt-1">Rs. {{ number_format($finalPrice, 2) }}</p>
                                    </div>

                                    <!-- Qty Changer -->
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                                        <button type="button" class="px-2.5 py-1 bg-gray-50 text-xs text-gray-600 hover:bg-gray-100" onclick="updateQty({{ $item->id }}, 'dec')">-</button>
                                        <input type="text" value="{{ $item->qty }}" readonly class="w-10 text-center text-xs border-0 py-1" id="qty-{{ $item->id }}">
                                        <button type="button" class="px-2.5 py-1 bg-gray-50 text-xs text-gray-600 hover:bg-gray-100" onclick="updateQty({{ $item->id }}, 'inc')">+</button>
                                    </div>

                                    <!-- Delete Item -->
                                    <button type="button" onclick="deleteItem({{ $item->id }})" class="text-gray-400 hover:text-red-500 text-sm ml-2">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Bottom Floating Bar -->
                <div class="bg-white rounded-xl shadow-sm p-4 sticky bottom-0 border-t border-gray-100 flex items-center justify-between mt-6">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300 text-[#1a2a6c] focus:ring-[#1a2a6c] h-4 w-4">
                        <span class="text-sm font-medium text-gray-700">All</span>
                    </label>

                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <span class="text-xs text-gray-500 block">Subtotal: <strong class="text-sm text-[#1a2a6c]" id="selected-total">Rs. 0.00</strong></span>
                        </div>
                        <button type="submit" class="px-6 py-2.5 bg-[#ff5722] hover:bg-[#f4511e] text-white text-sm font-semibold rounded-lg transition-all shadow-sm" id="checkout-btn">
                            Check Out (<span id="selected-count">0</span>)
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</section>

<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCb = document.getElementById('select-all');
        const cartCheckboxes = document.querySelectorAll('.cart-checkbox');
        const vendorCheckboxes = document.querySelectorAll('.vendor-checkbox');

        if(selectAllCb) {
            selectAllCb.addEventListener('change', function() {
                const isChecked = this.checked;
                cartCheckboxes.forEach(cb => cb.checked = isChecked);
                vendorCheckboxes.forEach(cb => cb.checked = isChecked);
                updateSummary();
            });
        }

        vendorCheckboxes.forEach(vendorCb => {
            vendorCb.addEventListener('change', function() {
                const vendorId = this.dataset.vendor;
                document.querySelectorAll('.vendor-item-' + vendorId).forEach(itemCb => itemCb.checked = this.checked);
                updateSummary();
            });
        });

        cartCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateSummary);
        });
    });

    function updateSummary() {
        let total = 0;
        let count = 0;
        document.querySelectorAll('.cart-checkbox:checked').forEach(cb => {
            total += parseFloat(cb.dataset.price) * parseInt(cb.dataset.qty);
            count++;
        });
        document.getElementById('selected-total').innerText = 'Rs. ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('selected-count').innerText = count;
    }

    function deleteItem(id) {
        if(confirm('Remove this item?')) {
            const form = document.getElementById('delete-form');
            form.action = '{{ url("cart") }}/' + id;
            form.submit();
        }
    }

    function updateQty(id, action) {
        let input = document.getElementById('qty-' + id);
        let qty = parseInt(input.value);
        if(action === 'inc') qty++;
        if(action === 'dec' && qty > 1) qty--;

        fetch('{{ url("cart") }}/' + id, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ qty: qty })
        }).then(res => { if(res.ok) location.reload(); });
    }
</script>
@endpush
@endsection