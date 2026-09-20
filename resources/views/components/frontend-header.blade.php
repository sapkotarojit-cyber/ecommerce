<header class="bg-white shadow-md sticky top-0 z-50">
    <!-- Top Bar -->
    <div class="bg-gradient-to-r from-[#0f1a3a] to-[#1e293b] text-white text-sm py-2">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="animate-pulse">🎉</span>
                    <span>Free Shipping on Medical & Surgical Orders $50+</span>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('orders.index') }}" class="hover:text-[#c9a84c] transition-colors">Orders</a>
                    <a href="{{ route('dokan_registration') }}" class="hover:text-[#c9a84c] transition-colors">Sell on Empire Innovation</a>
                    <a href="{{ route('orders.track') }}" class="hover:text-[#c9a84c] transition-colors">Track Order</a>                    
                    <a href="{{ route('support') }}" class="hover:text-[#c9a84c] transition-colors">Support</a>
                    <a href="{{ route('about') }}" class="hover:text-[#c9a84c] transition-colors">About Us</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between gap-4">
            <!-- Brand Logo -->
 <a href="{{ route('home') }}" class="flex items-center h-16 md:h-20 py-1">
    <img src="{{ asset('images/logo2.png') }}" alt="Empire Innovation" class="h-full w-auto object-contain">
</a>
            <!-- Search Bar - Desktop -->
            <div class="hidden md:block flex-1 max-w-xl">
                <form action="{{ route('products') }}" method="GET" class="relative group">
                    <input
                        type="text"
                        name="search"
                        placeholder="Search surgical supplies, equipment, or sellers..."
                        class="w-full px-5 py-2.5 border-2 border-gray-200 rounded-full focus:outline-none focus:border-[#0f1a3a] transition-all duration-300 text-sm"
                    >
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-[#0f1a3a] text-white p-2 rounded-full hover:bg-[#c9a84c] hover:text-[#0f1a3a] transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Action Icons -->
            <div class="flex items-center gap-4">
                <!-- Cart -->
                <a href="{{ route('cart.index') }}" class="relative group">
                    <div class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6 text-gray-700 group-hover:text-[#0f1a3a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </a>

                <!-- Account -->
                @auth
                    <div class="relative group hidden md:block">
                        <button class="flex items-center gap-2 p-2 rounded-full hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6 text-gray-700 group-hover:text-[#0f1a3a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-gray-700 font-medium group-hover:text-[#0f1a3a] text-sm">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-2 hidden group-hover:block z-50">
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#c9a84c]">My Orders</a>
                            <a href="{{ route('shipping-address.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#c9a84c]">Shipping Addresses</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#c9a84c]">Profile</a>
                            <a href="{{ route('settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#c9a84c]">Settings</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</a>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden md:flex items-center gap-2 p-2 rounded-full hover:bg-gray-100 transition-colors group">
                        <svg class="w-6 h-6 text-gray-700 group-hover:text-[#0f1a3a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-gray-700 font-medium group-hover:text-[#0f1a3a] text-sm">Login</span>
                    </a>
                @endauth

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
        <div class="p-4 space-y-3">
            <a href="{{ route('home') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">Home</a>
            <a href="{{ route('products') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">Products</a>
            <a href="{{ route('about') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">About Us</a>
            <a href="{{ route('dokan_registration') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">Become a Seller</a>
            <a href="{{ route('orders.track') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">Track Order</a>
            <a href="{{ route('support') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">Support</a>
            <div class="border-t border-gray-100 pt-3">
                @auth
                    <a href="{{ route('orders.index') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">My Orders</a>
                    <a href="{{ route('shipping-address.index') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">Shipping Addresses</a>
                    <a href="{{ route('logout') }}" class="block py-2 text-red-600 font-medium">Logout</a>
                    <a href="{{ route('profile.edit') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">Profile</a>
                    <a href="{{ route('settings') }}" class="block py-2 text-gray-700 hover:text-[#c9a84c] transition-colors">Settings</a>
                @else
                    <a href="{{ route('login') }}" class="block py-2 text-[#0f1a3a] font-semibold hover:text-[#c9a84c]">Login / Register</a>
                @endauth
            </div>
        </div>
    </div>
</header>