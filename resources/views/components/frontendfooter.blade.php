<!-- resources/views/components/frontend-footer.blade.php -->
<footer class="bg-[#1c253f] text-white/90">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-2 lg:col-span-1">
                <div class="mb-4">
                    <a href="{{ route('home') }}" class="inline-block h-12">
                        <img src="{{ asset('images/logo5.png') }}" alt="Empire Innovation" class="h-full w-auto object-contain">
                    </a>
                </div>
                <p class="text-sm text-white/70 leading-relaxed">
                    Empowering vendors and customers with a seamless multi-vendor ecommerce experience.
                </p>
                <div class="flex space-x-4 mt-4">
                    <a href="#" class="text-white/60 hover:text-[#c9a84c] transition-colors">
                        <i class="fab fa-facebook-f text-lg"></i>
                    </a>
                    <a href="#" class="text-white/60 hover:text-[#c9a84c] transition-colors">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                    <a href="#" class="text-white/60 hover:text-[#c9a84c] transition-colors">
                        <i class="fab fa-whatsapp text-lg"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">Home</a></li>
                    <li><a href="{{ route('products') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">Products</a></li>
                    <li><a href="{{ route('dokan_registration') }}" class="text-white/70 hover:text-[#c9a84c] transition-colors">Become a Vendor</a></li>
                    <li><a href="#" class="text-white/70 hover:text-[#c9a84c] transition-colors">About Us</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Support</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="text-white/70 hover:text-[#c9a84c] transition-colors">Help Center</a></li>
                    <li><a href="#" class="text-white/70 hover:text-[#c9a84c] transition-colors">Returns Policy</a></li>
                    <li><a href="#" class="text-white/70 hover:text-[#c9a84c] transition-colors">Shipping Info</a></li>
                    <li><a href="#" class="text-white/70 hover:text-[#c9a84c] transition-colors">Contact Us</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Contact</h3>
                <ul class="space-y-2 text-sm text-white/70">
                    <li><i class="fas fa-map-marker-alt text-[#c9a84c] w-5"></i> Kathmandu, Nepal</li>
                    <li><i class="fas fa-phone text-[#c9a84c] w-5"></i> +977-1-4444444</li>
                    <li><i class="fas fa-envelope text-[#c9a84c] w-5"></i> info@empireinnovation.com</li>
                </ul>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-white/10 mt-8 pt-6 text-center text-sm text-white/60">
            <p>&copy; {{ date('Y') }} Empireinnovation PVT. LTD. All rights reserved.</p>
        </div>
    </div>
</footer>