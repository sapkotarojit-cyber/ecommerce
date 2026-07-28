<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokan Approval - Empireinnovation</title>
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
        .credentials-box {
            background: #f8f7f4;
            border-left: 4px solid #c9a84c;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .credentials-box .label {
            font-weight: 600;
            color: #1a2a6c;
            display: inline-block;
            width: 120px;
        }
        .credentials-box .value {
            color: #333;
            font-weight: 500;
        }
        .button {
            display: inline-block;
            background: #c9a84c;
            color: #1a2a6c;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 15px;
            transition: background 0.3s;
        }
        .button:hover {
            background: #b8963a;
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
            <h2>🎉 Congratulations! Your Vendor Application is Approved</h2>

            <p>Dear <strong>{{ $data['company_name'] ?? 'Vendor' }}</strong>,</p>

            <p>We are pleased to inform you that your vendor application has been <strong>approved</strong> by our team. You can now start selling your products on our marketplace.</p>

            <div class="alert">
                <strong>📌 Important:</strong> Please save your login credentials securely. You will need them to access your vendor dashboard.
            </div>

            <!-- Credentials Box -->
            <div class="credentials-box">
                <h3 style="color: #1a2a6c; margin-top: 0; font-size: 16px;">🔑 Your Login Credentials</h3>

                <p>
                    <span class="label">Email:</span>
                    <span class="value">{{ $data['email'] ?? 'N/A' }}</span>
                </p>
                <p>
                    <span class="label">Password:</span>
                    <span class="value" style="background: #e8e8e8; padding: 2px 8px; border-radius: 4px; font-family: monospace;">
                        {{ $password ?? 'N/A' }}
                    </span>
                </p>
                <p style="font-size: 12px; color: #888; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> We recommend changing your password after first login.
                </p>
            </div>

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}/vendor/login" class="button">
                    🚀 Login to Vendor Dashboard
                </a>
            </p>

            <p style="color: #777; font-size: 14px; margin-top: 20px;">
                If you have any questions or need assistance, please don't hesitate to contact our support team.
            </p>

            <p style="color: #777; margin-top: 20px;">
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
                    <a href="mailto:support@empireinnovation.com" style="color: #1a2a6c; text-decoration: none;">Contact Support</a>
                </small>
            </p>
        </div>
    </div>
</body>
</html>
