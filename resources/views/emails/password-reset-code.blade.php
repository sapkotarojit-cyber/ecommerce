<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Verification Code</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">

    <div style="width:100%; padding:40px 0; background:#f4f6f8;">

        <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,0.08);">

            <!-- Header -->
            <div style="background:#0f1a3a; padding:28px 24px; text-align:center;">
                <h1 style="margin:0; color:#ffffff; font-size:24px;">
                    Empire Innovation
                </h1>
            </div>

            <!-- Content -->
            <div style="padding:36px 30px; text-align:center;">

                <h2 style="margin:0 0 12px; font-size:24px; color:#1f2937;">
                    Password Reset
                </h2>

                <p style="margin:0 0 24px; font-size:16px; line-height:1.6; color:#4b5563;">
                    We received a request to reset your Empire Innovation
                    account password.
                </p>

                <p style="margin:0 0 14px; font-size:15px; color:#4b5563;">
                    Your 6-digit verification code is:
                </p>

                <!-- Verification Code -->
                <div style="display:inline-block; padding:18px 28px; margin:8px 0 24px; background:#f3f4f6; border:2px solid #c9a84c; border-radius:10px;">
                    <span style="font-size:32px; font-weight:bold; letter-spacing:8px; color:#0f1a3a;">
                        {{ $code }}
                    </span>
                </div>

                <p style="margin:0 0 10px; font-size:14px; line-height:1.6; color:#6b7280;">
                    Enter this code in the Empire Innovation app to continue
                    resetting your password.
                </p>

                <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#6b7280;">
                    This verification code expires after 10 minutes.
                </p>

                <div style="height:1px; background:#e5e7eb; margin:24px 0;"></div>

                <p style="margin:0; font-size:13px; line-height:1.6; color:#9ca3af;">
                    If you did not request a password reset, you can safely
                    ignore this email. Your password will not be changed.
                </p>

            </div>

            <!-- Footer -->
            <div style="background:#f9fafb; padding:20px; text-align:center;">
                <p style="margin:0; font-size:12px; color:#9ca3af;">
                    © {{ date('Y') }} Empire Innovation. All rights reserved.
                </p>
            </div>

        </div>

    </div>

</body>
</html>
