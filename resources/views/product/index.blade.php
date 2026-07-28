<!-- resources/views/frontend/product/index.blade.php -->
@extends('frontend.frontend-layout')

@section('title', 'All Products - Empireinnovation')

@section('content')
<section class="py-8 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-[#c9a84c]">Home</a>
            <span class="mx-2">/</span>
            <span class="text-[#1a2a6c] font-medium">Products</span>
        </nav>
        
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar Filters -->
            <div class="w-full md:w-64 lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm p-5 sticky top-24">
                    <h3 class="font-bold text-[#1a2a6c] mb-4">Filters</h3>
                    
                    <div class="space-y-4">
                        <!-- Categories -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700 block mb-2">Category</label>
                            <select class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent text-sm">
                                <option>All Categories</option>
                                <option>Electronics</option>
                                <option>Fashion</option>
                                <option>Home & Garden</option>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700 block mb-2">Price Range</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" placeholder="Min" class="w-1/2 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent text-sm">
                                <span class="text-gray-400">-</span>
                                <input type="number" placeholder="Max" class="w-1/2 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent text-sm">
                            </div>
                        </div>
                        
                        <!-- Ratings -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700 block mb-2">Rating</label>
                            <div class="space-y-1">
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" class="rounded text-[#c9a84c] focus:ring-[#c9a84c]">
                                    <span class="ml-2 text-gray-600">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    </span>
                                </label>
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" class="rounded text-[#c9a84c] focus:ring-[#c9a84c]">
                                    <span class="ml-2 text-gray-600">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-gray-300"></i>
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <button class="w-full px-4 py-2 bg-[#1a2a6c] text-white font-semibold rounded-lg hover:bg-[#2a3a7c] transition-all">
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
                        <select class="px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#c9a84c] focus:border-transparent text-sm">
                            <option>Newest</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Popular</option>
                        </select>
                    </div>
                </div>
                
                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                                <a href="{{ route('product', $product->id) }}">
                                    <div class="relative h-56 bg-gray-100 overflow-hidden">
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
                                    </div>
                                </a>
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs text-[#c9a84c] font-semibold bg-[#c9a84c]/10 px-2 py-1 rounded-full">
                                            <i class="fas fa-store mr-1"></i> {{ $product->dokan->company_name ?? 'Vendor' }}
                                        </span>
                                        <div class="flex items-center text-yellow-400 text-xs">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                    </div>
                                    <a href="{{ route('product', $product->id) }}">
                                        <h3 class="font-semibold text-[#1a2a6c] hover:text-[#c9a84c] transition-colors line-clamp-1">
                                            {{ $product->title }}
                                        </h3>
                                    </a>
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                        <div>
                                            @if($product->varients->first())
                                                @php $varient = $product->varients->first(); @endphp
                                                @if($varient->discount > 0)
                                                    <span class="text-sm text-gray-400 line-through">Rs. {{ number_format($varient->price, 2) }}</span>
                                                    <span class="text-lg font-bold text-[#1a2a6c] ml-1">Rs. {{ number_format($varient->price - ($varient->price * $varient->discount / 100), 2) }}</span>
                                                @else
                                                    <span class="text-lg font-bold text-[#1a2a6c]">Rs. {{ number_format($varient->price, 2) }}</span>
                                                @endif
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
                        <p class="text-gray-500">No products found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection