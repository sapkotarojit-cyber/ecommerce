<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Too Many Requests - EmpireInnovation</title>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-50">

<div style="text-align:center;font-family:Arial,sans-serif;">
    <h2 style="color:#1a2a6c;">Too Many Requests</h2>

    <p style="color:#6b7280;">
        Please wait before trying again.
    </p>

    <div id="countdown"
         style="font-size:32px;font-weight:bold;color:#c9a84c;">
        {{ $retryAfter }}
    </div>

    <p style="color:#6b7280;">seconds</p>
</div>

<script>
let seconds = {{ $retryAfter }};

const countdown = document.getElementById('countdown');

const timer = setInterval(() => {
    seconds--;

    countdown.textContent = Math.max(seconds, 0);

    if (seconds <= 0) {
        clearInterval(timer);
        location.reload();
    }
}, 1000);
</script>

</body>
</html>