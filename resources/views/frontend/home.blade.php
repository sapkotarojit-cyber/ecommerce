<!-- resources/views/frontend/home.blade.php -->
@extends('frontend.frontend-layout')

@section('title', 'Empireinnovation - Multi-Vendor Marketplace')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-[#1a2a6c] via-[#2a3a7c] to-[#1a2a6c] text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <div>
                <span class="inline-block bg-[#c9a84c] text-[#1a2a6c] px-4 py-1 rounded-full text-sm font-semibold mb-4">
                    <i class="fas fa-store mr-2"></i> Multi-Vendor Marketplace
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
                    Shop from <span class="text-[#c9a84c]">Multiple Vendors</span> in One Place
                </h1>
                <p class="mt-4 text-lg text-white/80 max-w-lg">
                    Discover thousands of products from verified vendors across Nepal.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ route('products') }}" class="inline-flex items-center px-6 py-3 bg-[#c9a84c] text-[#1a2a6c] font-semibold rounded-full hover:bg-[#dbb95c] transition-all">
                        <i class="fas fa-shopping-bag mr-2"></i> Start Shopping
                    </a>
                    <a href="{{ route('dokan_registration') }}" class="inline-flex items-center px-6 py-3 border-2 border-white/30 text-white font-semibold rounded-full hover:bg-white/10 transition-all">
                        <i class="fas fa-store mr-2"></i> Sell on Empire
                    </a>
                </div>
            </div>
            <div class="hidden md:flex justify-center">
                <div class="relative">
                    <div class="w-64 h-64 bg-[#c9a84c]/20 rounded-full blur-3xl absolute -top-10 -right-10"></div>
                    <div class="grid grid-cols-2 gap-4 relative">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/10">
                            <i class="fas fa-store text-3xl text-[#c9a84c]"></i>
                            <p class="text-2xl font-bold mt-2">50+</p>
                            <p class="text-sm text-white/70">Vendors</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/10">
                            <i class="fas fa-box text-3xl text-[#c9a84c]"></i>
                            <p class="text-2xl font-bold mt-2">1000+</p>
                            <p class="text-sm text-white/70">Products</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/10">
                            <i class="fas fa-users text-3xl text-[#c9a84c]"></i>
                            <p class="text-2xl font-bold mt-2">5000+</p>
                            <p class="text-sm text-white/70">Happy Customers</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/10">
                            <i class="fas fa-truck text-3xl text-[#c9a84c]"></i>
                            <p class="text-2xl font-bold mt-2">Fast</p>
                            <p class="text-sm text-white/70">Delivery</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-12 md:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-[#1a2a6c]">Featured Products</h2>
                <p class="text-gray-500 mt-1">Handpicked products from our top vendors</p>
            </div>
            <a href="{{ route('products') }}" class="text-[#c9a84c] hover:text-[#b8963a] font-semibold flex items-center">
                View All <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        @if(isset($products) && $products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <a href="{{ route('product', $product->id) }}">
                            <div class="relative h-64 bg-gray-100 overflow-hidden">
                                @if($product->varients->first() && $product->varients->first()->images)
                                    @php $images = json_decode($product->varients->first()->images, true); @endphp
                                    @if($images && count($images) > 0)
                                        <img src="{{ asset('storage/' . $images[0]) }}" 
                                             alt="{{ $product->title }}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                            <i class="fas fa-image text-4xl text-gray-400"></i>
                                        </div>
                                    @endif
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                        <i class="fas fa-image text-4xl text-gray-400"></i>
                                    </div>
                                @endif
                                @if($product->varients->first() && $product->varients->first()->discount > 0)
                                    <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                        -{{ $product->varients->first()->discount }}%
                                    </span>
                                @endif
                            </div>
                        </a>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-[#c9a84c] font-semibold bg-[#c9a84c]/10 px-3 py-1 rounded-full">
                                    <i class="fas fa-store mr-1"></i> {{ $product->dokan->company_name ?? 'Vendor' }}
                                </span>
                                <div class="flex items-center text-yellow-400 text-sm">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                    <span class="text-gray-400 ml-1">(24)</span>
                                </div>
                            </div>
                            <a href="{{ route('product', $product->id) }}">
                                <h3 class="text-lg font-semibold text-[#1a2a6c] hover:text-[#c9a84c] transition-colors line-clamp-1">
                                    {{ $product->title }}
                                </h3>
                            </a>
                            <p class="text-gray-500 text-sm mt-1 line-clamp-2">{{ Str::limit($product->description, 80) }}</p>
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                <div>
                                    @if($product->varients->first())
                                        @php $varient = $product->varients->first(); @endphp
                                        @if($varient->discount > 0)
                                            <span class="text-sm text-gray-400 line-through">Rs. {{ number_format($varient->price, 2) }}</span>
                                            <span class="text-xl font-bold text-[#1a2a6c] ml-2">Rs. {{ number_format($varient->price - ($varient->price * $varient->discount / 100), 2) }}</span>
                                        @else
                                            <span class="text-xl font-bold text-[#1a2a6c]">Rs. {{ number_format($varient->price, 2) }}</span>
                                        @endif
                                    @endif
                                </div>
                                <a href="{{ route('product', $product->id) }}" class="inline-flex items-center px-4 py-2 bg-[#c9a84c] text-[#1a2a6c] font-semibold rounded-full hover:bg-[#dbb95c] transition-all text-sm">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-xl shadow-sm">
                <i class="fas fa-box-open text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No products available yet.</p>
            </div>
        @endif
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-[#1a2a6c]">Why Shop with Empireinnovation?</h2>
            <p class="text-gray-500 mt-2">Experience the best multi-vendor shopping experience</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-[#c9a84c]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-2xl text-[#c9a84c]"></i>
                </div>
                <h3 class="font-semibold text-[#1a2a6c] text-lg">Verified Vendors</h3>
                <p class="text-gray-500 text-sm mt-2">All vendors are verified for quality and authenticity</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-[#c9a84c]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-truck-fast text-2xl text-[#c9a84c]"></i>
                </div>
                <h3 class="font-semibold text-[#1a2a6c] text-lg">Fast Delivery</h3>
                <p class="text-gray-500 text-sm mt-2">Quick and reliable shipping across Nepal</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-[#c9a84c]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-2xl text-[#c9a84c]"></i>
                </div>
                <h3 class="font-semibold text-[#1a2a6c] text-lg">24/7 Support</h3>
                <p class="text-gray-500 text-sm mt-2">Dedicated support team always ready to help</p>
            </div>
        </div>
    </div>
</section>
@endsection