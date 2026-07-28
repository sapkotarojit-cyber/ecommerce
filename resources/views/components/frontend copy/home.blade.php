<!-- resources/views/frontend/home.blade.php -->
<x-frontend-layout>
    <!-- Hero Section -->
    <section class="relative overflow-hidden gradient-bg text-white">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-teal-400 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-4 py-16 md:py-24 relative">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div class="animate-fadeInUp">
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm mb-6">
                        <span>🏥</span>
                        <span>Empire Innovation PVT.LTD</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
                        Premium <span class="text-amber-300">Surgical Supplies</span> for Healthcare Excellence
                    </h1>
                    <p class="text-lg mb-6 text-sky-100">
                        Trusted by 500+ healthcare institutions. Get premium surgical instruments, medical equipment, and supplies from verified vendors.
                    </p>
                    <div class="flex flex-wrap gap-4 mb-6">
                        <span class="bg-white/10 px-4 py-2 rounded-full text-sm">📞 9860276013</span>
                        <span class="bg-white/10 px-4 py-2 rounded-full text-sm">📞 9818584007</span>
                        <span class="bg-white/10 px-4 py-2 rounded-full text-sm">✉️ empireinnovation2025@gmail.com</span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#" class="bg-amber-500 hover:bg-amber-600 text-white px-8 py-3 rounded-full font-semibold transition-all transform hover:scale-105 text-center">
                            Shop Now →
                        </a>
                        <a href="#" class="border-2 border-white hover:bg-white hover:text-sky-600 px-8 py-3 rounded-full font-semibold transition-all text-center">
                            Become a Supplier
                        </a>
                    </div>
                    <!-- Stats -->
                    <div class="flex gap-8 mt-8">
                        <div>
                            <div class="text-2xl font-bold">500+</div>
                            <div class="text-sm text-sky-200">Institutions</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">10K+</div>
                            <div class="text-sm text-sky-200">Products</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">98%</div>
                            <div class="text-sm text-sky-200">Satisfaction</div>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block animate-fadeInRight">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 text-center">
                        <div class="text-7xl mb-4 animate-float">🏥</div>
                        <p class="text-2xl font-bold">Empire Innovation</p>
                        <p class="text-sm text-sky-200">PVT.LTD</p>
                        <p class="text-sm text-sky-200 mt-2">Surgical & Medical Supplies</p>
                        <div class="mt-4 flex justify-center gap-4 text-xs">
                            <span class="bg-emerald-500/30 px-3 py-1 rounded-full">ISO Certified</span>
                            <span class="bg-emerald-500/30 px-3 py-1 rounded-full">FDA Approved</span>
                        </div>
                        <div class="mt-4 text-xs text-sky-200">
                            <p>📞 9860276013 / 9818584007</p>
                            <p>✉️ empireinnovation2025@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vendor Showcase (Based on Dokan Schema) -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12 animate-fadeInUp">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">Our <span class="gradient-text">Trusted Vendors</span></h2>
                <p class="text-slate-600 max-w-2xl mx-auto">Verified surgical suppliers with quality products</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $vendors = [
                        ['name' => 'MediTech Solutions', 'logo' => '🏥', 'products' => 345, 'rating' => 4.9, 'status' => 'approved', 'location' => 'Mumbai'],
                        ['name' => 'Surgical Innovations', 'logo' => '🔬', 'products' => 289, 'rating' => 4.8, 'status' => 'approved', 'location' => 'Delhi'],
                        ['name' => 'HealthCare Supplies', 'logo' => '💉', 'products' => 198, 'rating' => 4.7, 'status' => 'approved', 'location' => 'Bangalore'],
                        ['name' => 'MediEquip India', 'logo' => '⚕️', 'products' => 156, 'rating' => 4.6, 'status' => 'pending', 'location' => 'Chennai'],
                    ];
                @endphp
                @foreach($vendors as $vendor)
                <div class="bg-slate-50 rounded-xl p-6 text-center card-hover cursor-pointer border-2 {{ $vendor['status'] == 'approved' ? 'border-emerald-200' : 'border-amber-200' }}">
                    <div class="text-5xl mb-3">{{ $vendor['logo'] }}</div>
                    <h3 class="font-semibold text-lg">{{ $vendor['name'] }}</h3>
                    <p class="text-sm text-slate-500">{{ $vendor['products'] }} Products</p>
                    <p class="text-xs text-slate-400">{{ $vendor['location'] }}</p>
                    <div class="flex items-center justify-center gap-1 mt-2">
                        <span class="text-amber-400">★</span>
                        <span class="font-semibold">{{ $vendor['rating'] }}</span>
                        <span class="text-xs text-slate-400">(120 reviews)</span>
                    </div>
                    <span class="inline-block mt-3 px-3 py-1 rounded-full text-xs font-semibold {{ $vendor['status'] == 'approved' ? 'status-approved' : 'status-pending' }}">
                        {{ ucfirst($vendor['status']) }}
                    </span>
                    <a href="#" class="block mt-3 text-sky-600 hover:text-sky-800 text-sm font-semibold">
                        Visit Store →
                    </a>
                </div>
                @endforeach
            </div>
            <div class="text-center mt-8">
                <a href="#" class="text-sky-600 hover:text-sky-800 font-semibold">View All Vendors →</a>
            </div>
        </div>
    </section>

    <!-- Featured Products (Based on Products & Variants Schema) -->
    <section class="py-16 bg-slate-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold gradient-text">Featured Products</h2>
                <a href="#" class="text-sky-600 hover:text-sky-700 font-semibold">View All →</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $products = [
                        ['title' => 'Surgical Scissors Set', 'vendor' => 'MediTech Solutions', 'price' => 2499, 'discount' => 15, 'image' => '✂️', 'variants' => 3],
                        ['title' => 'Digital BP Monitor', 'vendor' => 'HealthCare Supplies', 'price' => 4999, 'discount' => 20, 'image' => '🩺', 'variants' => 2],
                        ['title' => 'Hospital Bed with Mattress', 'vendor' => 'Surgical Innovations', 'price' => 45999, 'discount' => 10, 'image' => '🛏️', 'variants' => 4],
                        ['title' => 'Surgical Mask Pack (100pcs)', 'vendor' => 'MediEquip India', 'price' => 999, 'discount' => 25, 'image' => '😷', 'variants' => 2],
                    ];
                @endphp
                @foreach($products as $product)
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover">
                    <div class="relative">
                        <div class="h-48 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-6xl">
                            {{ $product['image'] }}
                        </div>
                        @if($product['discount'] > 0)
                        <span class="absolute top-3 right-3 bg-red-500 text-white px-2 py-1 rounded-lg text-xs font-bold">
                            -{{ $product['discount'] }}%
                        </span>
                        @endif
                        <span class="absolute top-3 left-3 bg-sky-600 text-white px-2 py-1 rounded-lg text-xs font-bold">
                            {{ $product['variants'] }} Variants
                        </span>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-lg truncate">{{ $product['title'] }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-slate-500">By</span>
                            <span class="text-xs font-semibold text-sky-600">{{ $product['vendor'] }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-2xl font-bold text-sky-600">₹{{ number_format($product['price'] - ($product['price'] * $product['discount'] / 100)) }}</span>
                            <span class="text-sm text-slate-400 line-through">₹{{ number_format($product['price']) }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="bg-emerald-100 text-emerald-600 text-xs px-2 py-1 rounded-full">In Stock</span>
                            <span class="text-xs text-slate-500">Qty: 50+</span>
                        </div>
                        <button class="add-to-cart w-full mt-3 bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg transition-all font-semibold">
                            Add to Cart
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12 animate-fadeInUp">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">Shop by <span class="gradient-text">Category</span></h2>
                <p class="text-slate-600 max-w-2xl mx-auto">Browse our wide range of surgical and medical supplies</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @php
                    $categories = [
                        ['name' => 'Surgical Instruments', 'icon' => '🔪', 'count' => '1,200+'],
                        ['name' => 'Medical Equipment', 'icon' => '🩺', 'count' => '800+'],
                        ['name' => 'Hospital Furniture', 'icon' => '🛏️', 'count' => '500+'],
                        ['name' => 'Wound Care', 'icon' => '🩹', 'count' => '600+'],
                        ['name' => 'Diagnostic Tools', 'icon' => '🔬', 'count' => '400+'],
                        ['name' => 'Lab Supplies', 'icon' => '🧪', 'count' => '300+'],
                        ['name' => 'PPE & Safety', 'icon' => '🛡️', 'count' => '700+'],
                        ['name' => 'All Products', 'icon' => '📦', 'count' => '5,000+'],
                    ];
                @endphp
                @foreach($categories as $category)
                <a href="#" class="group bg-slate-50 rounded-xl p-6 text-center card-hover transition-all">
                    <div class="text-4xl mb-3 group-hover:scale-110 transition-transform">{{ $category['icon'] }}</div>
                    <h3 class="font-semibold text-slate-800 group-hover:text-sky-600 transition-colors">{{ $category['name'] }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ $category['count'] }} products</p>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Vendor CTA Section -->
    <section class="py-16 gradient-bg text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Become a <span class="text-amber-300">Supplier</span></h2>
            <p class="text-lg mb-8 max-w-2xl mx-auto text-sky-100">
                Join our platform and showcase your medical products to thousands of healthcare institutions
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="#" class="bg-amber-500 hover:bg-amber-600 text-white px-8 py-3 rounded-full font-semibold transition-all transform hover:scale-105">
                    Register as Vendor →
                </a>
                <a href="#" class="border-2 border-white hover:bg-white hover:text-sky-600 px-8 py-3 rounded-full font-semibold transition-all">
                    Learn More
                </a>
            </div>
            <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl mx-auto">
                <div class="bg-white/10 p-4 rounded-lg">
                    <div class="text-2xl font-bold">100+</div>
                    <div class="text-sm text-sky-200">Active Vendors</div>
                </div>
                <div class="bg-white/10 p-4 rounded-lg">
                    <div class="text-2xl font-bold">5K+</div>
                    <div class="text-sm text-sky-200">Products Listed</div>
                </div>
                <div class="bg-white/10 p-4 rounded-lg">
                    <div class="text-2xl font-bold">500+</div>
                    <div class="text-sm text-sky-200">Institutions</div>
                </div>
                <div class="bg-white/10 p-4 rounded-lg">
                    <div class="text-2xl font-bold">98%</div>
                    <div class="text-sm text-sky-200">Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl font-bold text-slate-800 mb-4">Need <span class="gradient-text">Bulk Orders</span>?</h2>
                <p class="text-slate-600 mb-8">Get special pricing for hospitals, clinics, and bulk purchases</p>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-slate-50 p-6 rounded-xl card-hover">
                        <div class="text-3xl mb-3">📞</div>
                        <h3 class="font-semibold">Call Us</h3>
                        <p class="text-sm text-slate-600 mt-2">9860276013</p>
                        <p class="text-sm text-slate-600">9818584007</p>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-xl card-hover">
                        <div class="text-3xl mb-3">✉️</div>
                        <h3 class="font-semibold">Email Us</h3>
                        <p class="text-sm text-slate-600 mt-2">empireinnovation2025@gmail.com</p>
                    </div>
                    <div class="bg-slate-50 p-6 rounded-xl card-hover">
                        <div class="text-3xl mb-3">💬</div>
                        <h3 class="font-semibold">Live Chat</h3>
                        <p class="text-sm text-slate-600 mt-2">24/7 Support Available</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-frontend-layout>
