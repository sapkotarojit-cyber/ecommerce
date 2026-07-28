<x-frontend-layout>

@stack('styles')
</head>
<body>
    <!-- Simple Header -->
    <header class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
        <div class="container-custom">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="text-xl font-bold text-primary-500">
                    Empireinnovation
                </a>
                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-sm text-gray-700">{{ Auth::user()->name ?? 'User' }}</span>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                           class="text-sm text-gray-600 hover:text-primary-500 transition-colors">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="GET" class="hidden"></form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-primary-500 transition-colors">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Simple Footer -->
    <footer class="bg-primary-500 text-white mt-auto">
        <div class="container-custom py-6 text-center text-sm text-white/60">
            &copy; {{ date('Y') }} Empireinnovation PVT.LTD. All rights reserved.
        </div>
    </footer>

    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>


</x-frontend-layout>
