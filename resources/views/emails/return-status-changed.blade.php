<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Return Request Status Updated</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f5f5; padding:30px 15px;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" border="0"
                   style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden;">

                {{-- Header --}}
                <tr>
                    <td style="background:#111827; padding:25px; text-align:center;">
                        <h1 style="margin:0; color:#ffffff; font-size:24px;">
                            EmpireInnovation
                        </h1>

                        <p style="margin:8px 0 0; color:#d1d5db; font-size:14px;">
                            Return Request Update
                        </p>
                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td style="padding:35px 30px;">

                        <h2 style="margin:0 0 15px; color:#111827; font-size:20px;">
                            Your return request has been updated
                        </h2>

                        <p style="margin:0 0 20px; color:#4b5563; font-size:15px; line-height:1.6;">
                            Hello {{ $returnRequest->user?->name ?? 'Customer' }},
                        </p>

                        <p style="margin:0 0 25px; color:#4b5563; font-size:15px; line-height:1.6;">
                            The status of your return request for
                            <strong>Order #{{ $returnRequest->order_id }}</strong>
                            has been changed.
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background:#f9fafb; border-radius:8px; margin-bottom:25px;">

                            <tr>
                                <td style="padding:15px; color:#6b7280; font-size:14px;">
                                    Previous Status
                                </td>

                                <td style="padding:15px; text-align:right; font-weight:bold; color:#374151;">
                                    {{ ucfirst($oldStatus) }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:15px; border-top:1px solid #e5e7eb; color:#6b7280; font-size:14px;">
                                    New Status
                                </td>

                                <td style="padding:15px; border-top:1px solid #e5e7eb; text-align:right; font-weight:bold; color:#111827;">
                                    {{ ucfirst($newStatus) }}
                                </td>
                            </tr>

                        </table>

                        @if($returnRequest->admin_note)
                            <div style="background:#f9fafb; padding:15px; border-radius:8px; margin-bottom:25px;">
                                <strong style="color:#374151;">Note:</strong>

                                <p style="margin:8px 0 0; color:#4b5563; line-height:1.5;">
                                    {{ $returnRequest->admin_note }}
                                </p>
                            </div>
                        @endif

                        <p style="margin:0; color:#6b7280; font-size:14px; line-height:1.6;">
                            Thank you for shopping with EmpireInnovation.
                        </p>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background:#f9fafb; padding:20px 30px; text-align:center;">

                        <p style="margin:0; color:#9ca3af; font-size:12px;">
                            This is an automated email from EmpireInnovation.
                        </p>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>