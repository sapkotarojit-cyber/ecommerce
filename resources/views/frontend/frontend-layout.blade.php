<!-- resources/views/frontend/frontend-layout.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Empireinnovation PVT.LTD - @yield('title', 'Multi-Vendor Marketplace')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-[#f8f7f4]">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <x-frontend-header/>
        
        <!-- Main Content -->
        <main class="flex-grow">
            @yield('content')
        </main>
        
        <!-- Footer -->
        <x-frontend-footer/>
    </div>
    
    @stack('scripts')
</body>
</html>