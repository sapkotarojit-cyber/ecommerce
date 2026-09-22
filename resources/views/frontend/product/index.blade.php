@extends('frontend.frontend-layout')

@section('title', 'All Products - Empireinnovation')
<title>Products - EmpireInnovation</title>

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-[#c9a84c]">Home</a>
            <span class="mx-2">/</span>
            <span class="text-[#1a2a6c] font-medium">Products</span>
        </nav>
        
        <!-- Main Form Wrapper for Filters and Sort -->
        <form action="{{ route('products') }}" method="GET" id="filterForm">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Sidebar Filters -->
                <div class="w-full md:w-64 lg:w-72 flex-shrink-0">
                    <div class="bg-white rounded-xl shadow-sm p-5 sticky top-24">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-[#1a2a6c]">Filters</h3>
                            @if(request()->hasAny(['category', 'min_price', 'max_price', 'sort']))
                                <a href="{{ route('products') }}" class="text-xs text-red-500 hover:underline">Clear All</a>
                            @endif
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Categories Filter -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 block mb-2">Category</label>
                                <select name="category" onchange="document.getElementById('filterForm').submit();" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent text-sm">
                                    <option value="">All Categories</option>
                                    @if(isset($categories) && count($categories) > 0)
                                        @foreach($categories as $cat)
                                            @php
                                                $catValue = is_object($cat) ? ($cat->id ?? $cat->slug ?? $cat->name) : $cat;
                                                $catLabel = is_object($cat) ? ($cat->name ?? $cat->title) : $cat;
                                            @endphp
                                            <option value="{{ $catValue }}" {{ (string)request('category') === (string)$catValue ? 'selected' : '' }}>
                                                {{ ucfirst($catLabel) }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            
                            <!-- Price Range -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 block mb-2">Price Range</label>
                                <div class="flex items-center space-x-2">
                                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-1/2 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent text-sm">
                                    <span class="text-gray-400">-</span>
                                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-1/2 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent text-sm">
                                </div>
                            </div>
                            
                            <button type="submit" class="w-full px-4 py-2 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-all">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="flex-1">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-[#1a2a6c]">All Products</h1>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm text-gray-500">Sort by:</label>
                            <select name="sort" onchange="document.getElementById('filterForm').submit();" class="px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent text-sm">
                                <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                    
                    @if($products->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($products as $product)
                                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                                    <a href="{{ route('product', $product->id) }}">
                                        <div class="relative h-56 bg-gray-100 overflow-hidden">
                                            @php
                                                $variant = $product->varients->first();
                                                $imageUrl = null;

                                                if ($variant && !empty($variant->images)) {
                                                    $rawImages = $variant->images;
                                                    
                                                    if (is_array($rawImages)) {
                                                        $images = $rawImages;
                                                    } elseif (is_string($rawImages)) {
                                                        $decoded = json_decode($rawImages, true);
                                                        $images = is_array($decoded) ? $decoded : [$rawImages];
                                                    } else {
                                                        $images = [];
                                                    }

                                                    if (!empty($images[0])) {
                                                        $path = $images[0];
                                                        $imageUrl = filter_var($path, FILTER_VALIDATE_URL) ? $path : asset('storage/' . ltrim($path, '/'));
                                                    }
                                                }
                                            @endphp

                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" 
                                                     alt="{{ $product->title }}" 
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            @else
                                                <div class="w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-400">
                                                    <i class="fas fa-image text-4xl mb-1"></i>
                                                    <span class="text-xs">No Image Available</span>
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                    <div class="p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs text-[#c9a84c] font-semibold bg-[#c9a84c]/10 px-2 py-1 rounded-full">
                                                <i class="fas fa-store mr-1"></i> {{ $product->dokan->company_name ?? $product->dokan->name ?? 'Vendor' }}
                                            </span>
                                        </div>
                                        <a href="{{ route('product', $product->id) }}">
                                            <h3 class="font-semibold text-[#1a2a6c] hover:text-[#c9a84c] transition-colors line-clamp-1">
                                                {{ $product->title }}
                                            </h3>
                                        </a>
                                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                            <div>
                                                @if($variant)
                                                    @php 
                                                        $price = $variant->price ?? 0;
                                                        $discount = $variant->discount ?? 0;
                                                    @endphp
                                                    @if($discount > 0)
                                                        <span class="text-sm text-gray-400 line-through">Rs. {{ number_format($price, 2) }}</span>
                                                        <span class="text-lg font-bold text-[#1a2a6c] ml-1">Rs. {{ number_format($price - ($price * $discount / 100), 2) }}</span>
                                                    @else
                                                        <span class="text-lg font-bold text-[#1a2a6c]">Rs. {{ number_format($price, 2) }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-sm text-gray-400">Price unavailable</span>
                                                @endif
                                            </div>
                                            <a href="{{ route('product', $product->id) }}" class="text-[#c9a84c] hover:text-[#b8963a]">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-8">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="text-center py-16 bg-white rounded-xl shadow-sm">
                            <i class="fas fa-box-open text-5xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No products match your search filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</section>
@endsection