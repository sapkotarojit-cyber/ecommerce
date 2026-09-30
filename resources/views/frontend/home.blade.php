@extends('frontend.frontend-layout')

@section('title', 'Empireinnovation - Multi-Vendor Marketplace')
<title>Home - EmpireInnovation</title>


@section('content')

{{-- HERO SECTION --}}
<section class="relative w-full max-w-full overflow-hidden bg-gradient-to-r from-[#1a2a6c] via-[#2a3a7c] to-[#1a2a6c] text-white">

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 md:py-24">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-center min-w-0">

            <div class="w-full min-w-0">

                <span class="inline-flex max-w-full items-center bg-[#c9a84c] text-[#1a2a6c] px-4 py-1.5 rounded-full text-xs sm:text-sm font-semibold mb-5">
                    <i class="fas fa-store mr-2 shrink-0"></i>
                    <span>Multi-Vendor Marketplace</span>
                </span>

                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold leading-tight break-words">
                    Shop from
                    <span class="text-[#c9a84c]">
                        Multiple Vendors
                    </span>
                    in One Place
                </h1>

                <p class="mt-4 text-base sm:text-lg text-white/80 max-w-lg leading-relaxed">
                    Discover thousands of products from verified vendors across Nepal.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:gap-4 w-full">

                    <a href="{{ route('products') }}"
                       class="inline-flex w-full sm:w-auto items-center justify-center px-6 py-3 bg-[#c9a84c] text-[#1a2a6c] font-semibold rounded-full hover:bg-[#dbb95c] transition-all">

                        <i class="fas fa-shopping-bag mr-2"></i>

                        Start Shopping

                    </a>

                    <a href="{{ route('dokan_registration') }}"
                       class="inline-flex w-full sm:w-auto items-center justify-center px-6 py-3 border-2 border-white/30 text-white font-semibold rounded-full hover:bg-white/10 transition-all">

                        <i class="fas fa-store mr-2"></i>

                        Sell on Empire

                    </a>

                </div>

            </div>


            {{-- HERO STATISTICS --}}
            <div class="hidden md:flex justify-center min-w-0">

                <div class="relative">

                    <div class="w-64 h-64 bg-[#c9a84c]/20 rounded-full blur-3xl absolute -top-10 -right-10 pointer-events-none"></div>

                    <div class="grid grid-cols-2 gap-4 relative">

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/10">
                            <i class="fas fa-store text-3xl text-[#c9a84c]"></i>

                            <p class="text-2xl font-bold mt-2">
                                50+
                            </p>

                            <p class="text-sm text-white/70">
                                Vendors
                            </p>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/10">
                            <i class="fas fa-box text-3xl text-[#c9a84c]"></i>

                            <p class="text-2xl font-bold mt-2">
                                1000+
                            </p>

                            <p class="text-sm text-white/70">
                                Products
                            </p>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/10">
                            <i class="fas fa-users text-3xl text-[#c9a84c]"></i>

                            <p class="text-2xl font-bold mt-2">
                                5000+
                            </p>

                            <p class="text-sm text-white/70">
                                Happy Customers
                            </p>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/10">
                            <i class="fas fa-truck text-3xl text-[#c9a84c]"></i>

                            <p class="text-2xl font-bold mt-2">
                                Fast
                            </p>

                            <p class="text-sm text-white/70">
                                Delivery
                            </p>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


{{-- FEATURED PRODUCTS --}}
<section class="w-full max-w-full overflow-hidden py-10 sm:py-12 md:py-16">

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- SECTION HEADER --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-8">

            <div class="min-w-0">

                <h2 class="text-2xl md:text-3xl font-bold text-[#1a2a6c]">
                    Featured Products
                </h2>

                <p class="text-gray-500 mt-1 text-sm sm:text-base">
                    Handpicked products from our top vendors
                </p>

            </div>

            <a href="{{ route('products') }}"
               class="shrink-0 text-[#c9a84c] hover:text-[#b8963a] font-semibold flex items-center">

                View All

                <i class="fas fa-arrow-right ml-2"></i>

            </a>

        </div>


        @if(isset($products) && $products->count() > 0)

            {{-- MOBILE 3 | TABLET 2 | DESKTOP 4 --}}
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-4 gap-3 sm:gap-6">

                @foreach($products as $product)

                    @php

                        $firstVariant = $product->varients->first();

                        $imageUrl = null;

                        $discount = $firstVariant->discount ?? 0;

                        if ($firstVariant && !empty($firstVariant->images)) {

                            $rawImages = $firstVariant->images;

                            if (is_array($rawImages)) {

                                $images = $rawImages;

                            } elseif (is_string($rawImages)) {

                                $decoded = json_decode($rawImages, true);

                                $images = is_array($decoded)
                                    ? $decoded
                                    : [$rawImages];

                            } else {

                                $images = [];

                            }

                            if (!empty($images[0])) {

                                $path = trim(
                                    str_replace(
                                        ['\\', '"', '[', ']'],
                                        '',
                                        $images[0]
                                    )
                                );

                                $imageUrl = filter_var($path, FILTER_VALIDATE_URL)
                                    ? $path
                                    : asset(
                                        'storage/' . ltrim($path, '/')
                                    );
                            }
                        }

                        $variant = $firstVariant;

                        $price = $variant->price ?? 0;

                        $discountedPrice = $price;

                        if ($discount > 0) {

                            $discountedPrice =
                                $price -
                                (($price * $discount) / 100);

                        }

                        $vendorName =
                            $product->dokan->company_name
                            ?? $product->dokan->name
                            ?? 'Empire';

                    @endphp


                    {{-- PRODUCT CARD --}}
                    <div class="w-full min-w-0 bg-white rounded-lg sm:rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group flex flex-col justify-between">

                        <div class="min-w-0">

                            {{-- PRODUCT IMAGE --}}
                            <a href="{{ route('product', $product->id) }}"
                               class="block">

                                <div class="relative w-full h-36 sm:h-56 lg:h-64 bg-gray-100 overflow-hidden">

                                    @if($imageUrl)

                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $product->title }}"
                                            loading="lazy"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        >

                                    @else

                                        <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-400">

                                            <i class="fas fa-image text-2xl sm:text-4xl"></i>

                                        </div>

                                    @endif


                                    {{-- VENDOR BADGE - TOP LEFT --}}
                                    <div class="absolute top-2 left-2 z-10 max-w-[48%]">

                                        <span class="inline-flex items-center max-w-full px-1.5 sm:px-2.5 py-1 bg-white/95 text-[#c9a84c] text-[8px] sm:text-xs font-semibold rounded-full shadow-sm">

                                            <i class="fas fa-store mr-1 text-[7px] sm:text-[9px] shrink-0"></i>

                                            <span class="truncate">
                                                {{ $vendorName }}
                                            </span>

                                        </span>

                                    </div>


                                    {{-- DISCOUNT BADGE - TOP RIGHT --}}
                                    @if($discount > 0)

                                        <div class="absolute top-2 right-2 z-10">

                                            <span class="inline-flex items-center px-1.5 sm:px-2.5 py-1 bg-green-500 text-white text-[8px] sm:text-xs font-bold rounded-md shadow-sm whitespace-nowrap">

                                                {{ $discount }}% OFF

                                            </span>

                                        </div>

                                    @endif

                                </div>

                            </a>


                            {{-- PRODUCT INFORMATION --}}
                            <div class="p-2 sm:p-4">

                                <a href="{{ route('product', $product->id) }}">

                                    <h3 class="font-semibold text-[#1a2a6c] hover:text-[#c9a84c] transition-colors line-clamp-2 break-words text-[10px] sm:text-base leading-tight sm:leading-normal">

                                        {{ $product->title }}

                                    </h3>

                                </a>

                            </div>

                        </div>


                        {{-- PRICE FOOTER --}}
                        <div class="px-2 pb-2 sm:px-4 sm:pb-4">

                            <div class="flex items-center justify-between pt-2 sm:pt-3 border-t border-gray-100 gap-1 sm:gap-3">

                                <div class="min-w-0">

                                    @if($variant)

                                        @if($discount > 0)

                                            <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:gap-2">

                                                <span class="text-[8px] sm:text-sm text-gray-400 line-through whitespace-nowrap">

                                                    Rs. {{ number_format($price, 2) }}

                                                </span>

                                                <span class="text-xs sm:text-lg font-bold text-[#1a2a6c] whitespace-nowrap">

                                                    Rs. {{ number_format($discountedPrice, 2) }}

                                                </span>

                                            </div>

                                        @else

                                            <span class="text-xs sm:text-lg font-bold text-[#1a2a6c] whitespace-nowrap">

                                                Rs. {{ number_format($price, 2) }}

                                            </span>

                                        @endif

                                    @else

                                        <span class="text-[9px] sm:text-sm text-gray-400">
                                            Price unavailable
                                        </span>

                                    @endif

                                </div>


                                {{-- PRODUCT ARROW --}}
                                <a href="{{ route('product', $product->id) }}"
                                   aria-label="View {{ $product->title }}"
                                   class="shrink-0 text-[#c9a84c] hover:text-[#b8963a] transition-transform group-hover:translate-x-1">

                                    <i class="fas fa-arrow-right text-[10px] sm:text-base"></i>

                                </a>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="text-center py-16 px-4 bg-white rounded-xl shadow-sm">

                <i class="fas fa-box-open text-5xl text-gray-300 mb-4"></i>

                <p class="text-gray-500">
                    No featured products available at the moment.
                </p>

            </div>

        @endif

    </div>

</section>

@endsection