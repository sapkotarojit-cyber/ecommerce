@extends('frontend.frontend-layout')

@section('title', $product->title . ' - Empireinnovation')
<title>Products - EmpireInnovation</title>

@section('content')
<section class="py-8 md:py-12" x-data="cartComponent()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-[#c9a84c]">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('products') }}" class="hover:text-[#c9a84c]">Products</a>
            <span class="mx-2">/</span>
            <span class="text-[#1a2a6c] font-medium">{{ $product->title }}</span>
        </nav>

        <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Product Gallery -->
                <div>
                    @php
                        $firstVariant = $product->varients->first();
                        $rawImages = $firstVariant?->images;

                        if (is_array($rawImages)) {
                            $images = $rawImages;
                        } elseif (is_string($rawImages)) {
                            $decoded = json_decode($rawImages, true);
                            $images = is_array($decoded) ? $decoded : [$rawImages];
                        } else {
                            $images = [];
                        }

                        $images = array_map(function($img) {
                            return trim(str_replace(['\\', '"', '[', ']'], '', $img));
                        }, array_filter($images));
                    @endphp

                    <div class="mb-4 h-96 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                        @if(!empty($images) && isset($images[0]))
                            <img id="main-image" src="{{ asset('storage/' . $images[0]) }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-center text-gray-400">
                                <i class="fas fa-image text-5xl mb-2"></i>
                                <p>No image available</p>
                            </div>
                        @endif
                    </div>

                    @if(!empty($images) && count($images) > 1)
                        <div class="flex gap-2 overflow-x-auto pb-2">
                            @foreach($images as $image)
                                <button type="button" onclick="document.getElementById('main-image').src='{{ asset('storage/' . $image) }}'" class="w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent focus:border-[#c9a84c] flex-shrink-0">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Thumbnail" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Product Info & Actions -->
                <div class="flex flex-col justify-between">
                    <div>
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-xs text-[#c9a84c] font-semibold bg-[#c9a84c]/10 px-3 py-1 rounded-full">
                                <i class="fas fa-store mr-1"></i> {{ $product->dokan->company_name ?? 'Vendor' }}
                            </span>
                        </div>

                        <h1 class="text-3xl font-bold text-[#1a2a6c] mb-4">{{ $product->title }}</h1>

                        <!-- Pricing -->
                        <div class="mb-6">
                            @if($firstVariant)
                                @if($firstVariant->discount > 0)
                                    <div class="flex items-baseline space-x-3">
                                        <span class="text-2xl font-bold text-[#1a2a6c]">
                                            Rs. {{ number_format($firstVariant->price - ($firstVariant->price * $firstVariant->discount / 100), 2) }}
                                        </span>
                                        <span class="text-gray-400 line-through">Rs. {{ number_format($firstVariant->price, 2) }}</span>
                                        <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded font-semibold">{{ $firstVariant->discount }}% OFF</span>
                                    </div>
                                @else
                                    <span class="text-2xl font-bold text-[#1a2a6c]">Rs. {{ number_format($firstVariant->price, 2) }}</span>
                                @endif
                            @endif
                        </div>

                        <!-- HTML Rendered Description -->
                        <div class="border-t border-b border-gray-100 py-4 my-4 text-gray-600 leading-relaxed">
                            {!! $product->description ?? 'No description available for this product.' !!}
                        </div>
                    </div>
                            
                    <!-- Add to Cart Form -->
                    @if($firstVariant)
                        <form @submit.prevent="addToCart" class="mt-6">
                           <input type="hidden" x-model="varient_id" value="{{ $firstVariant?->id }}">

                            <div class="flex items-center gap-4 mb-6">
                                <label class="text-sm font-semibold text-gray-700">Quantity:</label>
                                <input type="number" x-model.number="qty" value="1" min="1" max="{{ $firstVariant->qty }}" class="w-20 px-3 py-2 border border-gray-200 rounded-lg text-center focus:ring-2 focus:ring-[#c9a84c]">
                                <span class="text-xs text-gray-500">({{ $firstVariant->qty }} in stock)</span>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-4">
                                <button type="submit" :disabled="loading || buyNowLoading" class="flex-1 py-3 px-6 bg-[#1a2a6c] hover:bg-[#2a3a7c] text-white font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                                    <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-shopping-cart'"></i>
                                    <span class="text-white font-semibold" x-text="loading ? 'Adding...' : 'Add to Cart'">Add to Cart</span>
                                </button>

                                <button type="button" @click="buyNow" :disabled="loading || buyNowLoading" class="flex-1 py-3 px-6 bg-[#c9a84c] hover:bg-[#b8963a] text-white font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                                    <i class="fas" :class="buyNowLoading ? 'fa-spinner fa-spin' : 'fa-bolt'"></i>
                                    <span class="text-white font-semibold" x-text="buyNowLoading ? 'Processing...' : 'Buy Now'">Buy Now</span>
                                </button>
                                
                                <a href="{{ route('cart.index') }}" class="py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 whitespace-nowrap" title="View Cart">
                                    <i class="fas fa-shopping-bag"></i>
                                </a>
                            </div>
                        </form>
                    @else
                        <p class="text-red-500 font-semibold mt-4">Out of stock / No variant available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Added to Cart Popup Modal -->
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-center relative">
            <button @click="showModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-[#1a2a6c] mb-2">Item has been added to cart</h3>
            <p class="text-gray-600 text-sm mb-6">
                "<span class="font-semibold text-gray-800">{{ $product->title }}</span>" has been successfully added to your cart.
            </p>
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="showModal = false" class="w-full py-2.5 px-4 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors text-sm">
                    Continue Shopping
                </button>
                <a href="{{ route('cart.index') }}" class="w-full py-2.5 px-4 bg-[#c9a84c] text-white rounded-lg font-semibold hover:bg-[#b8963a] transition-colors text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-shopping-bag"></i>
                    <span>View Cart</span>
                </a>
            </div>
        </div>
    </div>
</section>

<script>
    function cartComponent() {
        return {
            varient_id: "{{ $firstVariant?->id }}",
            qty: 1,
            loading: false,
            buyNowLoading: false,
            showModal: false,
            async addToCart() {
                @if(!Auth::check())
                    alert('Please log in first to add items to your cart.');
                    window.location.href = "{{ route('login') }}";
                    return;
                @endif

                this.loading = true;

                try {
                    let response = await fetch("{{ route('cart.add') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "X-Requested-With": "XMLHttpRequest",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            varient_id: this.varient_id,
                            qty: this.qty
                        })
                    });

                    if (response.redirected || response.status === 401 || response.status === 419) {
                        alert('Your session has expired. Please log in again.');
                        window.location.href = "{{ route('login') }}";
                        return;
                    }

                    let data = await response.json();
                    this.loading = false;

                    if (data.success) {
                        this.showModal = true;
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
                    } else {
                        alert(data.message || 'Error updating cart.');
                    }
                } catch (error) {
                    this.loading = false;
                    console.error('Fetch Error:', error);
                    alert('An error occurred while adding the item to your cart.');
                }
            },
            async buyNow() {
                @if(!Auth::check())
                    alert('Please log in first to proceed with checkout.');
                    window.location.href = "{{ route('login') }}";
                    return;
                @endif

                this.buyNowLoading = true;

                try {
                    let response = await fetch("{{ route('cart.buy-now') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "X-Requested-With": "XMLHttpRequest",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            varient_id: this.varient_id,
                            qty: this.qty
                        })
                    });

                    if (response.redirected || response.status === 401 || response.status === 419) {
                        alert('Your session has expired. Please log in again.');
                        window.location.href = "{{ route('login') }}";
                        return;
                    }

                    let data = await response.json();
                    this.buyNowLoading = false;

                    if (data.success && data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || 'Error processing your request.');
                    }
                } catch (error) {
                    this.buyNowLoading = false;
                    console.error('Fetch Error:', error);
                    alert('An error occurred.');
                }
            }
        }
    }
</script>
@endsection