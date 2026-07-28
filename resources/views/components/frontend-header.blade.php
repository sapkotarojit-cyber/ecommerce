<!-- resources/views/components/frontend-header.blade.php -->
<header class="bg-white shadow-md sticky top-0 z-50">
    <!-- Top bar -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm py-2">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="animate-pulse">🎉</span>
                    <span>Free Shipping on Orders $50+</span>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="#" class="hover:text-amber-300 transition-colors">Sell on MarketHub</a>
                    <a href="#" class="hover:text-amber-300 transition-colors">Track Order</a>
                    <a href="#" class="hover:text-amber-300 transition-colors">Support</a>
                    <a href="#" class="hover:text-amber-300 transition-colors">Become a Vendor</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main header -->
    <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between gap-4">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2">
                <div class="text-2xl md:text-3xl font-bold gradient-text">
                    MarketHub
                </div>
                <span class="hidden md:inline-block text-xs bg-indigo-100 text-indigo-600 px-2 py-1 rounded-full font-semibold">
                    Multi-Vendor
                </span>
            </a>

            <!-- Search bar - Desktop -->
            <div class="hidden md:block flex-1 max-w-xl">
                <div class="relative group">
                    <input
                        type="text"
                        placeholder="Search products, brands, or vendors..."
                        class="w-full px-5 py-3 border-2 border-gray-200 rounded-full focus:outline-none focus:border-indigo-500 transition-all duration-300"
                    >
                    <button class="absolute right-2 top-1/2 -translate-y-1/2 bg-indigo-600 text-white p-2 rounded-full hover:bg-indigo-700 transition-all duration-200 hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Icons -->
            <div class="flex items-center gap-4">
                <!-- Cart -->
                <a href="#" class="relative group">
                    <div class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <span class="absolute -top-1 -right-1 bg-amber-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse-slow">
                        3
                    </span>
                </a>

                <!-- Account -->
                <a href="#" class="hidden md:flex items-center gap-2 p-2 rounded-full hover:bg-gray-100 transition-colors group">
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-gray-700 group-hover:text-indigo-600">Account</span>
                </a>

                <!-- Mobile menu button -->
                <button id="mobile-menu-button" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
        <div class="p-4 space-y-3">
            <div class="relative">
                <input type="text" placeholder="Search..." class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500">
            </div>
            <a href="#" class="block py-2 text-gray-600 hover:text-indigo-600 transition-colors">Sell on MarketHub</a>
            <a href="#" class="block py-2 text-gray-600 hover:text-indigo-600 transition-colors">Track Order</a>
            <a href="#" class="block py-2 text-gray-600 hover:text-indigo-600 transition-colors">Support</a>
            <a href="#" class="block py-2 text-gray-600 hover:text-indigo-600 transition-colors">Account</a>
            <a href="#" class="block py-2 text-gray-600 hover:text-indigo-600 transition-colors">Become a Vendor</a>
        </div>
    </div>

    <!-- Vendor Stats Bar -->
    <div class="border-t bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-2 text-xs md:text-sm">
                <div class="flex items-center gap-4 md:gap-8 overflow-x-auto">
                    <span class="font-semibold text-gray-700">🏪 Top Vendors:</span>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 whitespace-nowrap">TechStore</a>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 whitespace-nowrap">FashionHub</a>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 whitespace-nowrap">HomeDecor</a>
                    <a href="#" class="text-indigo-600 hover:text-indigo-800 whitespace-nowrap">SportZone</a>
                </div>
                <span class="hidden md:inline text-gray-500">100+ Active Vendors</span>
            </div>
        </div>
    </div>
</header>
