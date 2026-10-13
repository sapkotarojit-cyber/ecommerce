<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6f9;font-family:Arial,sans-serif;color:#1e293b;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;">
        <h1 style="margin-top:0;color:#0f1a3a;">Payment Successful</h1>

        <p>
            Hello {{ $orders->first()?->user?->name ?? 'Customer' }},
        </p>

        <p>
            Your payment has been successfully confirmed.
            Thank you for shopping with Empire Innovation.
        </p>

        @foreach ($orders as $order)
            <div style="padding:16px;background:#f8fafc;border-radius:8px;margin:20px 0;">
                <h3 style="margin-top:0;">Order {{ $order->tracking_number }}</h3>

                <p><strong>Payment method:</strong> {{ strtoupper($order->payment_method) }}</p>
                <p><strong>Payment status:</strong> Paid</p>
                <p><strong>Order status:</strong> {{ ucfirst($order->order_status) }}</p>
                <p><strong>Order amount:</strong> NPR {{ number_format((float) $order->total_amount, 2) }}</p>

                @if ($order->dokan)
                    <p><strong>Vendor:</strong> {{ $order->dokan->name }}</p>
                @endif
            </div>
        @endforeach

        <p>You can check your orders through your account.</p>
        <p>Thank you for choosing Empire Innovation.</p>
    </div>
</body>
</html>
