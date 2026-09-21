<footer class="bg-[#1c253f] text-white/90 mt-auto">
    <!-- Seller CTA Banner -->
    <div class="border-b border-white/10 bg-gradient-to-r from-slate-900/40 to-amber-900/20">
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h4 class="text-white font-semibold text-lg">Want to sell medical & surgical supplies on Empire Innovation?</h4>
                    <p class="text-sm text-white/70">Join 100+ verified medical sellers and grow your business today</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Footer Body -->
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Brand Overview -->
            <div class="col-span-1 md:col-span-2 lg:col-span-1">
                <!-- Replaced text logo with image logo -->
                <div class="mb-4">
                    <a href="{{ route('home') }}" class="inline-block h-12">
                        <img src="{{ asset('images/logo5.png') }}" alt="Empire Innovation" class="h-full w-auto ">
                    </a>
                </div>
                <p class="text-sm text-white/70 leading-relaxed mb-4">
                    Empowering surgical suppliers, healthcare institutions, and sellers with a seamless multi-seller ecommerce experience.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">Home</a></li>
                    <li><a href="{{ route('products') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">Products</a></li>
                    <li><a href="{{ route('about') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">About Us</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Customer Support</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('support') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">Help & Contact</a></li>
                    <li><a href="{{ route('orders.track') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">Track Your Order</a></li>
                    <li><a href="{{ route('cart.index') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">View Cart</a></li>
                    <li><a href="{{ route('dokan_registration') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">Seller Registration</a></li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div>
                <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Contact Us</h4>
                <ul class="space-y-3 text-sm text-white/70">
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-[#c9a84c] mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Kathmandu, Nepal</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#c9a84c] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <a href="https://wa.me/9779860276013" target="_blank" class="hover:text-[#c9a84c] transition-colors">+977 9860276013 / 9818584007</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#c9a84c] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <a href="mailto:empireinnovation2025@gmail.com" class="hover:text-[#c9a84c] transition-colors">empireinnovation2025@gmail.com</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bottom Legal Bar -->
    <div class="border-t border-white/10">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-white/60">
                <p>&copy; {{ date('Y') }} Empireinnovation PVT. LTD. All rights reserved.</p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <a href="{{ route('about') }}" class="hover:text-[#c9a84c] transition-colors">About Us</a>
                    <a href="{{ route('support') }}" class="hover:text-[#c9a84c] transition-colors">Support</a>
                    <a href="{{ route('dokan_registration') }}" class="hover:text-[#c9a84c] transition-colors">Seller Terms</a>
                </div>
            </div>
        </div>
    </div>
</footer>