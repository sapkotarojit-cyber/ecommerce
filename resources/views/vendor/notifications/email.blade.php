<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0;background:#f5f6fa;font-family:Arial,sans-serif;color:#333;">
    <div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">

        <div style="background:#1a2a6c;padding:25px;text-align:center;">
            <h1 style="margin:0;color:#fff;font-size:24px;">
                EmpireInnovation
            </h1>
        </div>

        <div style="padding:35px;">
            @if (!empty($greeting))
                <h2 style="color:#1a2a6c;margin-top:0;">{{ $greeting }}</h2>
            @else
                <h2 style="color:#1a2a6c;margin-top:0;">Hello!</h2>
            @endif

            @foreach ($introLines as $line)
                <p style="color:#666;line-height:1.7;">{{ $line }}</p>
            @endforeach

            @isset($actionText)
                <div style="text-align:center;margin:30px 0;">
                    <a href="{{ $actionUrl }}"
                       style="background:#1a2a6c;color:#fff;text-decoration:none;padding:14px 28px;border-radius:8px;font-weight:bold;display:inline-block;">
                        {{ $actionText }}
                    </a>
                </div>
            @endisset

            @foreach ($outroLines as $line)
                <p style="color:#666;line-height:1.7;">{{ $line }}</p>
            @endforeach

            <p style="color:#777;margin-top:30px;">
                Regards,<br>
                <strong style="color:#1a2a6c;">EmpireInnovation</strong>
            </p>
        </div>

        <div style="background:#fafafa;border-top:1px solid #eee;padding:18px;text-align:center;">
            <p style="margin:0;color:#999;font-size:12px;">
                © {{ date('Y') }} EmpireInnovation. All rights reserved.
            </p>
        </div>

    </div>
</body>
</html>