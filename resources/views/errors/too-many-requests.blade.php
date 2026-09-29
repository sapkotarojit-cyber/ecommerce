<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Too Many Requests</title>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow text-center max-w-md">
        <h1 class="text-2xl font-bold mb-3">Too Many Requests</h1>

        <p class="text-gray-600">
            Please wait {{ $retryAfter }} seconds before trying again.
        </p>
    </div>
</body>
</html>