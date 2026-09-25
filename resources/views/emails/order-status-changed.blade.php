<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Order Status Updated</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif;">

<div style="max-width:700px; margin:30px auto; background:#ffffff; border-radius:10px; overflow:hidden;">

    <div style="background:#1a2a6c; padding:25px; text-align:center;">

        <h1 style="color:#ffffff; margin:0;">
            Empire Innovation
        </h1>

        <p style="color:#ffffff; margin:8px 0 0;">
            Order Status Update
        </p>

    </div>

    <div style="padding:30px;">

        <h2 style="color:#1a2a6c;">
            Hello {{ $order->user->name ?? 'Customer' }},
        </h2>

        <p style="color:#555;">
            Your order status has been updated.
        </p>

        <div style="text-align:center; margin:30px 0;">

            <p style="color:#777; margin-bottom:8px;">
                Order Status
            </p>

            <div style="
                display:inline-block;
                padding:12px 25px;
                background:#f1f1f1;
                border-radius:30px;
                font-size:20px;
                font-weight:bold;
                color:#1a2a6c;
            ">
                {{ ucfirst($newStatus) }}
            </div>

        </div>

        <div style="background:#f8f9fa; padding:15px; border-radius:8px;">

            <p style="margin:5px 0;">
                <strong>Order Number:</strong>
                {{ $order->tracking_number }}
            </p>

            <p style="margin:5px 0;">
                <strong>Previous Status:</strong>
                {{ ucfirst($oldStatus) }}
            </p>

            <p style="margin:5px 0;">
                <strong>New Status:</strong>
                {{ ucfirst($newStatus) }}
            </p>

            <p style="margin:5px 0;">
                <strong>Vendor:</strong>
                {{ $order->dokan->company_name ?? 'Vendor' }}
            </p>

        </div>

        <h3 style="color:#1a2a6c; margin-top:30px;">
            Order Products
        </h3>

        <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse;">

            <thead>
                <tr style="background:#f1f1f1;">
                    <th align="left">Product</th>
                    <th align="center">Qty</th>
                    <th align="right">Amount</th>
                </tr>
            </thead>

            <tbody>

                @foreach($order->orderItems as $item)

                    <tr style="border-bottom:1px solid #eeeeee;">

                        <td>

                            <strong>
                                {{ $item->product->title ?? 'Product' }}
                            </strong>

                            @if($item->varient)
                                <br>

                                <small style="color:#777;">
                                    {{ $item->varient->title ?? '' }}
                                </small>
                            @endif

                        </td>

                        <td align="center">
                            {{ $item->qty }}
                        </td>

                        <td align="right">
                            Rs. {{ number_format($item->amount, 2) }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

        <div style="text-align:right; margin-top:20px;">

            <h2 style="color:#1a2a6c;">
                Total: Rs. {{ number_format($order->total_amount, 2) }}
            </h2>

        </div>

        @if($newStatus === 'processing')

            <p style="margin-top:25px; color:#555;">
                Your order is now being processed by the vendor.
            </p>

        @elseif($newStatus === 'completed')

            <p style="margin-top:25px; color:#555;">
                Your order has been completed.
                Thank you for shopping with Empire Innovation!
            </p>

        @elseif($newStatus === 'cancelled')

            <p style="margin-top:25px; color:#555;">
                Unfortunately, your order has been cancelled.
            </p>

        @endif

    </div>

    <div style="background:#f1f1f1; padding:20px; text-align:center; color:#777;">

        <p style="margin:0;">
            © {{ date('Y') }} Empire Innovation
        </p>

    </div>

</div>

</body>
</html>