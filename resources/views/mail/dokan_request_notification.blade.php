<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Vendor Registration</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f6f9; padding: 40px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">

        <!-- Header -->
        <div style="background: #1e3c4f; padding: 30px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 24px;">Empireinnovation PVT.LTD</h1>
            <p style="margin: 4px 0 0; opacity: 0.7; font-size: 14px;">Multi-Vendor Marketplace</p>
            <div style="display: inline-block; margin-top: 12px; background: rgba(255,255,255,0.15); padding: 4px 16px; border-radius: 20px; font-size: 12px; border: 1px solid rgba(255,255,255,0.1);">
                ⚡ New Vendor Request
            </div>
        </div>

        <!-- Body -->
        <div style="padding: 30px 30px 20px;">
            <h2 style="color: #1e3c4f; margin: 0 0 4px;">Dear Admin,</h2>
            <p style="color: #6b7280; margin-top: 4px;">A new vendor has submitted a registration request.</p>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;">

            <h3 style="color: #1e3c4f; margin-bottom: 12px; font-size: 16px;">📋 Vendor Details</h3>

            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="padding: 8px 0; color: #6b7280; font-weight: 600; width: 40%;">Company Name:</td>
                    <td style="padding: 8px 0; color: #1f2937; font-weight: 500;">{{ $dokan->company_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Contact Person:</td>
                    <td style="padding: 8px 0; color: #1f2937; font-weight: 500;">{{ $dokan->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Email:</td>
                    <td style="padding: 8px 0; color: #1f2937; font-weight: 500;">{{ $dokan->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Contact Number:</td>
                    <td style="padding: 8px 0; color: #1f2937; font-weight: 500;">{{ $dokan->contact_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Registration No:</td>
                    <td style="padding: 8px 0; color: #1f2937; font-weight: 500;">{{ $dokan->reg_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Status:</td>
                    <td style="padding: 8px 0; color: #d97706; font-weight: 600;">⏳ Pending Review</td>
                </tr>
            </table>

            @if(isset($dokan->logo) && $dokan->logo)
            <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin: 16px 0; text-align: center; border: 2px dashed #e5e7eb;">
                <p style="margin: 0; font-size: 13px; color: #6b7280;">Company Logo:</p>
                <img src="{{ asset('storage/' . $dokan->logo) }}" alt="Logo" style="max-width: 100px; max-height: 100px; border-radius: 8px; margin-top: 8px;">
            </div>
            @endif

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;">

            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="#" style="display: inline-block; background: #1e3c4f; color: #ffffff; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">Review Application</a>
                <a href="#" style="display: inline-block; background: transparent; color: #1e3c4f; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; border: 2px solid #1e3c4f;">Contact Vendor</a>
            </div>

            <p style="color: #6b7280; font-size: 13px; margin-top: 16px;">
                <strong>Note:</strong> Please review the application and take appropriate action.
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0; color: #6b7280; font-size: 13px;">
                &copy; {{ date('Y') }} Empireinnovation PVT.LTD. All rights reserved.
            </p>
            <p style="margin: 4px 0 0; color: #9ca3af; font-size: 12px;">
                This is an automated notification. Please do not reply to this email.
            </p>
        </div>

    </div>
</body>
</html>
