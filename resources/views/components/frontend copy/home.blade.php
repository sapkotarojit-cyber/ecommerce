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
                        Premium <span class="text-[#c9a84c]">Surgical Supplies</span> for Healthcare Excellence
                    </h1>
                    <p class="text-lg mb-6 text-sky-100">
                        Trusted by 500+ healthcare institutions. Get premium surgical instruments, medical equipment, and supplies directly from verified sellers.
                    </p>
                    <div class="flex flex-wrap gap-3 mb-6">
                        <span class="bg-white/10 px-4 py-2 rounded-full text-sm flex items-center gap-1.5">📞 9860276013</span>
                        <span class="bg-white/10 px-4 py-2 rounded-full text-sm flex items-center gap-1.5">📞 9818584007</span>
                        <span class="bg-white/10 px-4 py-2 rounded-full text-sm flex items-center gap-1.5">✉️ empireinnovation2025@gmail.com</span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('products') }}" class="bg-[#c9a84c] hover:bg-[#b08e35] text-[#0f1a3a] px-8 py-3 rounded-full font-semibold transition-all transform hover:scale-105 text-center shadow-lg">
                            Shop Now →
                        </a>
                        <a href="{{ route('dokan_registration') }}" class="border-2 border-white hover:bg-white hover:text-[#0f1a3a] px-8 py-3 rounded-full font-semibold transition-all text-center">
                            Become a Seller
                        </a>
                    </div>
                    <!-- Stats Section -->
                    <div class="flex gap-8 mt-8">
                        
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
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 text-center border border-white/20 shadow-2xl">
                        <div class="text-7xl mb-4 animate-float">🏥</div>
                        <p class="text-2xl font-bold">Empire Innovation</p>
                        <p class="text-sm text-sky-200">PVT.LTD</p>
                        <p class="text-sm text-sky-200 mt-2">Surgical & Medical Supplies</p>
                        <div class="mt-4 flex justify-center gap-4 text-xs">
                            <span class="bg-emerald-500/30 text-emerald-200 px-3 py-1 rounded-full font-medium">ISO Certified</span>
                            <span class="bg-emerald-500/30 text-emerald-200 px-3 py-1 rounded-full font-medium">FDA Approved</span>
                        </div>
                        <div class="mt-6 text-xs text-sky-200 space-y-1">
                            <p>📞 +977 9860276013 / 9818584007</p>
                            <p>✉️ empireinnovation2025@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seller Showcase Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12 animate-fadeInUp">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">
                    Our <span class="gradient-text">Trusted Sellers</span>
                </h2>
                <p class="text-slate-600 max-w-2xl mx-auto">
                    Verified surgical and medical suppliers with high-quality certified equipment
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $sellers = [
                        ['name' => 'MediTech Solutions', 'logo' => '🏥', 'products' => 345, 'rating' => 4.9, 'status' => 'approved', 'location' => 'Kathmandu'],
                        ['name' => 'Surgical Innovations', 'logo' => '🔬', 'products' => 289, 'rating' => 4.8, 'status' => 'approved', 'location' => 'Pokhara'],
                        ['name' => 'HealthCare Supplies', 'logo' => '💉', 'products' => 198, 'rating' => 4.7, 'status' => 'approved', 'location' => 'Lalitpur'],
                        ['name' => 'MediEquip Nepal', 'logo' => '⚕️', 'products' => 156, 'rating' => 4.6, 'status' => 'pending', 'location' => 'Chitwan'],
                    ];
                @endphp

                @foreach($sellers as $seller)
                <div class="bg-slate-50 rounded-xl p-6 text-center card-hover cursor-pointer border-2 {{ $seller['status'] == 'approved' ? 'border-emerald-200' : 'border-amber-200' }}">
                    <div class="text-5xl mb-3">{{ $seller['logo'] }}</div>
                    <h3 class="font-semibold text-lg text-slate-800">{{ $seller['name'] }}</h3>
                    <p class="text-sm text-slate-500">{{ $seller['products'] }} Products</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $seller['location'] }}</p>
                    
                    <div class="flex items-center justify-center gap-1 mt-3">
                        <span class="text-amber-400">★</span>
                        <span class="font-semibold text-slate-700 text-sm">{{ $seller['rating'] }}</span>
                        <span class="text-xs text-slate-400">(120 reviews)</span>
                    </div>

                    <div class="mt-4">
                        @if($seller['status'] == 'approved')
                            <span class="inline-block px-3 py-1 text-xs rounded-full font-semibold status-approved">
                                Verified Seller
                            </span>
                        @else
                            <span class="inline-block px-3 py-1 text-xs rounded-full font-semibold status-pending">
                                Application Pending
                            </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</x-frontend-layout>