<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error - {{ config('app.name', 'Empire Innovation') }}</title>
    <meta name="robots" content="noindex,nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <main class="w-full max-w-lg rounded-2xl bg-white p-8 text-center shadow-lg">
        <h1 class="text-2xl font-bold text-gray-900">Something went wrong</h1>
        <p class="mt-3 text-gray-600">{{ $message }}</p>
        <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-lg bg-[#1a2a6c] px-5 py-3 font-semibold text-white">Return home</a>
    </main>
</body>
</html>
