@extends('frontend.frontend-layout')

@section('title', $product->title . ' - Empireinnovation')

<title>Show Product - EmpireInnovation</title>


@section('content')
<section class="py-8 md:py-12" x-data="cartComponent()">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="flex text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-[#c9a84c]">
                Home
            </a>

            <span class="mx-2">/</span>

            <a href="{{ route('products') }}" class="hover:text-[#c9a84c]">
                Products
            </a>

            <span class="mx-2">/</span>

            <span class="text-[#1a2a6c] font-medium">
                {{ $product->title }}
            </span>
        </nav>

        <!-- Product -->
        <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <!-- Gallery -->
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

                        $images = array_map(
                            fn($img) => trim(
                                str_replace(
                                    ['\\', '"', '[', ']'],
                                    '',
                                    $img
                                )
                            ),
                            array_filter($images)
                        );
                    @endphp

                    <!-- Main Image -->
                    <div class="mb-4 h-96 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">

                        @if(!empty($images))
                            <img
                                id="main-image"
                                src="{{ asset('storage/' . $images[0]) }}"
                                alt="{{ $product->title }}"
                                class="w-full h-full object-cover"
                            >
                        @else
                            <div class="text-center text-gray-400">
                                <i class="fas fa-image text-5xl mb-2"></i>
                                <p>No image available</p>
                            </div>
                        @endif

                    </div>

                    <!-- Thumbnails -->
                    @if(count($images) > 1)
                        <div class="flex gap-2 overflow-x-auto pb-2">

                            @foreach($images as $image)

                                <button
                                    type="button"
                                    onclick="document.getElementById('main-image').src='{{ asset('storage/' . $image) }}'"
                                    class="w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-[#c9a84c] flex-shrink-0"
                                >
                                    <img
                                        src="{{ asset('storage/' . $image) }}"
                                        alt="Thumbnail"
                                        class="w-full h-full object-cover"
                                    >
                                </button>

                            @endforeach

                        </div>
                    @endif

                </div>

                <!-- Product Information -->
                <div class="flex flex-col justify-between">

                    <div>

                        <!-- Vendor -->
                        <div class="flex items-center space-x-2 mb-2">

                            <span class="text-xs text-[#c9a84c] font-semibold bg-[#c9a84c]/10 px-3 py-1 rounded-full">

                                <i class="fas fa-store mr-1"></i>

                                {{ $product->dokan->company_name ?? 'Vendor' }}

                            </span>

                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl font-bold text-[#1a2a6c] mb-4">
                            {{ $product->title }}
                        </h1>

                        <!-- Price -->
                        @if($firstVariant)

                            @php
                                $price = (float) $firstVariant->price;
                                $discount = (float) $firstVariant->discount;
                                $finalPrice = $price - ($price * $discount / 100);
                            @endphp

                            <div class="mb-6">

                                @if($discount > 0)

                                    <div class="flex items-baseline gap-3">

                                        <span class="text-2xl font-bold text-[#1a2a6c]">
                                            Rs. {{ number_format($finalPrice, 2) }}
                                        </span>

                                        <span class="text-gray-400 line-through">
                                            Rs. {{ number_format($price, 2) }}
                                        </span>

                                        <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded font-semibold">
                                            {{ $discount }}% OFF
                                        </span>

                                    </div>

                                @else

                                    <span class="text-2xl font-bold text-[#1a2a6c]">
                                        Rs. {{ number_format($price, 2) }}
                                    </span>

                                @endif

                            </div>

                        @endif

                        <!-- Description -->
                        <div class="border-t border-b border-gray-100 py-4 my-4 text-gray-600 leading-relaxed">
                            {!! $product->description ?? 'No description available for this product.' !!}
                        </div>

                    </div>

                    <!-- Cart -->
                    @if($firstVariant)

                        <form @submit.prevent="addToCart" class="mt-6">

                            <input
                                type="hidden"
                                x-model="varient_id"
                                value="{{ $firstVariant->id }}"
                            >

                            <!-- Quantity -->
                            <div class="flex items-center gap-4 mb-6">

                                <label class="text-sm font-semibold text-gray-700">
                                    Quantity:
                                </label>

                                <input
                                    type="number"
                                    x-model.number="qty"
                                    min="1"
                                    max="{{ $firstVariant->qty }}"
                                    class="w-20 px-3 py-2 border border-gray-200 rounded-lg text-center focus:ring-2 focus:ring-[#c9a84c]"
                                >

                                <span class="text-xs text-gray-500">
                                    ({{ $firstVariant->qty }} in stock)
                                </span>

                            </div>

                            <!-- Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4">

                                <button
                                    type="submit"
                                    :disabled="loading || buyNowLoading"
                                    class="flex-1 py-3 px-6 bg-[#1a2a6c] hover:bg-[#2a3a7c] text-white font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                                >
                                    <i
                                        class="fas"
                                        :class="loading ? 'fa-spinner fa-spin' : 'fa-shopping-cart'"
                                    ></i>

                                    <span
                                        x-text="loading ? 'Adding...' : 'Add to Cart'"
                                    >
                                        Add to Cart
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    @click="buyNow"
                                    :disabled="loading || buyNowLoading"
                                    class="flex-1 py-3 px-6 bg-[#c9a84c] hover:bg-[#b8963a] text-white font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                                >
                                    <i
                                        class="fas"
                                        :class="buyNowLoading ? 'fa-spinner fa-spin' : 'fa-bolt'"
                                    ></i>

                                    <span
                                        x-text="buyNowLoading ? 'Processing...' : 'Buy Now'"
                                    >
                                        Buy Now
                                    </span>
                                </button>

                                <a
                                    href="{{ route('cart.index') }}"
                                    class="py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2"
                                >
                                    <i class="fas fa-shopping-bag"></i>
                                </a>

                            </div>

                        </form>

                    @else

                        <p class="text-red-500 font-semibold mt-4">
                            Out of stock / No variant available.
                        </p>

                    @endif

                </div>

            </div>

        </div>

        <!-- ========================================================= -->
        <!-- RELATED PRODUCTS -->
        <!-- ========================================================= -->

        @if($relatedProducts->count())

            <section class="mt-10">

                <div class="flex items-center justify-between mb-6">

                    <h2 class="text-2xl font-bold text-[#1a2a6c]">
                        You May Also Like
                    </h2>

                    <a
                        href="{{ route('products') }}"
                        class="text-sm font-semibold text-[#c9a84c] hover:underline"
                    >
                        View All
                    </a>

                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">

                    @foreach($relatedProducts as $item)

                        @php
                            $variant = $item->varients->first();
                            $price = (float)($variant?->price ?? 0);
                            $discount = (float)($variant?->discount ?? 0);
                            $finalPrice = $price - ($price * $discount / 100);

                            $raw = $variant?->images;

                            if (is_array($raw)) {
                                $relatedImages = $raw;
                            } elseif (is_string($raw)) {
                                $decoded = json_decode($raw, true);
                                $relatedImages = is_array($decoded)
                                    ? $decoded
                                    : [$raw];
                            } else {
                                $relatedImages = [];
                            }

                            $relatedImage = trim(
                                str_replace(
                                    ['\\', '"', '[', ']'],
                                    '',
                                    $relatedImages[0] ?? ''
                                )
                            );
                        @endphp

                        <a
                            href="{{ url('/product/' . $item->id) }}"
                            class="bg-white rounded-xl shadow-sm hover:shadow-lg transition overflow-hidden group"
                        >

                            <!-- Image -->
                            <div class="relative h-52 bg-gray-100 overflow-hidden">

                                @if($relatedImage)

                                    <img
                                        src="{{ asset('storage/' . $relatedImage) }}"
                                        alt="{{ $item->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                    >

                                @else

                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <i class="fas fa-image text-4xl"></i>
                                    </div>

                                @endif

                                <!-- Discount Top Right -->
                                @if($discount > 0)

                                    <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                        {{ $discount }}% OFF
                                    </span>

                                @endif

                            </div>

                            <!-- Details -->
                            <div class="p-4">

                                <h3 class="font-semibold text-[#1a2a6c] truncate">
                                    {{ $item->title }}
                                </h3>

                                <div class="mt-2">

                                    @if($discount > 0)

                                        <span class="font-bold text-[#1a2a6c]">
                                            Rs. {{ number_format($finalPrice, 2) }}
                                        </span>

                                        <span class="ml-2 text-xs text-gray-400 line-through">
                                            Rs. {{ number_format($price, 2) }}
                                        </span>

                                    @else

                                        <span class="font-bold text-[#1a2a6c]">
                                            Rs. {{ number_format($price, 2) }}
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </a>

                    @endforeach

                </div>

            </section>

        @endif

    </div>

    <!-- ========================================================= -->
    <!-- CART SUCCESS MODAL -->
    <!-- ========================================================= -->

    <div
        x-show="showModal"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        style="display:none;"
    >

        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-center relative">

            <button
                @click="showModal = false"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
            >
                <i class="fas fa-times text-lg"></i>
            </button>

            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-2xl"></i>
            </div>

            <h3 class="text-xl font-bold text-[#1a2a6c] mb-2">
                Item has been added to cart
            </h3>

            <p class="text-gray-600 text-sm mb-6">
                "{{ $product->title }}" has been successfully added to your cart.
            </p>

            <div class="flex flex-col sm:flex-row gap-3">

                <button
                    @click="showModal = false"
                    class="w-full py-2.5 px-4 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50"
                >
                    Continue Shopping
                </button>

                <a
                    href="{{ route('cart.index') }}"
                    class="w-full py-2.5 px-4 bg-[#c9a84c] text-white rounded-lg font-semibold hover:bg-[#b8963a] flex items-center justify-center gap-2"
                >
                    <i class="fas fa-shopping-bag"></i>
                    View Cart
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
                window.location.href = "{{ route('login') }}";
                return;
            @endif

            this.loading = true;

            try {
                const response = await fetch("{{ route('cart.add') }}", {
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

                if ([401, 419].includes(response.status) || response.redirected) {
                    window.location.href = "{{ route('login') }}";
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    this.showModal = true;

                    window.dispatchEvent(
                        new CustomEvent('cart-updated', {
                            detail: { count: data.cart_count }
                        })
                    );
                } else {
                    alert(data.message || 'Error updating cart.');
                }

            } catch (error) {
                alert('An error occurred while adding the item to your cart.');
            } finally {
                this.loading = false;
            }
        },

        async buyNow() {
            @if(!Auth::check())
                window.location.href = "{{ route('login') }}";
                return;
            @endif

            this.buyNowLoading = true;

            try {
                const response = await fetch("{{ route('cart.buy-now') }}", {
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

                if ([401, 419].includes(response.status) || response.redirected) {
                    window.location.href = "{{ route('login') }}";
                    return;
                }

                const data = await response.json();

                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'Error processing your request.');
                }

            } catch (error) {
                alert('An error occurred.');
            } finally {
                this.buyNowLoading = false;
            }
        }
    };
}
</script>

@endsection