<!-- resources/views/frontend/frontend-layout.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Clean Favicon Definitions -->
    <link rel="icon" type="image/png" href="{{ asset('logo1.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo1.png') }}">
    <link rel="shortcut icon" href="{{ asset('logo1.png') }}">

    <meta name="description" content="Shop medical and surgical supplies from thousands of trusted sellers on Empire Innovation">
    <title>Empire Innovation - Multi-Seller Marketplace</title>      
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