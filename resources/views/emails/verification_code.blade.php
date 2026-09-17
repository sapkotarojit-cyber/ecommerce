<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Empireinnovation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a2a6c 0%, #2a3a7c 100%);
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header p {
            color: #c9a84c;
            margin: 5px 0 0;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #1a2a6c;
            margin-top: 0;
            font-size: 20px;
        }
        .content p {
            color: #555;
            line-height: 1.6;
            margin: 10px 0;
        }
        .code-box {
            background: #f8f7f4;
            border-left: 4px solid #c9a84c;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
            text-align: center;
        }
        .code-box .code {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 8px;
            color: #1a2a6c;
            font-family: 'Courier New', Courier, monospace;
        }
        .footer {
            background: #f8f7f4;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            color: #888;
            font-size: 12px;
            margin: 5px 0;
        }
        .footer .brand {
            color: #1a2a6c;
            font-weight: 600;
        }
        .footer .brand span {
            color: #c9a84c;
        }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 6px;
            margin: 15px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🏪 Empireinnovation</h1>
            <p>PVT. LTD</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>✉️ Verify Your Email Address</h2>

            <p>Thank you for registering with <strong>Empireinnovation</strong>. Please use the following 6-digit verification code to complete your registration:</p>

            <!-- Verification Code Box -->
            <div class="code-box">
                <span class="code">{{ $code }}</span>
            </div>

            <div class="alert">
                <strong>⏰ Notice:</strong> This code will expire in 10 minutes. If you did not request this verification code, please ignore this email.
            </div>

            <p style="color: #777; margin-top: 25px;">
                Best regards,<br>
                <strong style="color: #1a2a6c;">Empireinnovation PVT. LTD</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="brand">Empireinnovation <span>PVT. LTD</span></p>
            <p>&copy; {{ date('Y') }} All rights reserved.</p>
            <p>
                <small>
                    <a href="{{ config('app.url') }}" style="color: #1a2a6c; text-decoration: none;">Visit our website</a> |
                    <a href="mailto:empireinnovation2025@gmail.com" style="color: #1a2a6c; text-decoration: none;">Contact Support</a>
                </small>
            </p>
        </div>
    </div>
</body>
</html>