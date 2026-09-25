<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>Order Placed</title>
</head>

<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif;">

<div style="max-width:700px; margin:30px auto; background:#ffffff; border-radius:10px; overflow:hidden;">

    <!-- Header -->
    <div style="background:#1a2a6c; padding:25px; text-align:center;">
        <h1 style="color:#ffffff; margin:0;">
            Empire Innovation
        </h1>

        <p style="color:#ffffff; margin:8px 0 0;">
            Order Confirmation
        </p>
    </div>

    <!-- Content -->
    <div style="padding:30px;">

        <h2 style="color:#1a2a6c;">
            Thank you for your order, {{ $order->user->name ?? 'Customer' }}!
        </h2>

        <p style="color:#555;">
            Your order has been successfully placed.
        </p>

        <!-- Order Information -->
        <div style="background:#f8f9fa; padding:15px; border-radius:8px; margin:20px 0;">

            <p style="margin:5px 0;">
                <strong>Order Number:</strong>
                {{ $order->tracking_number }}
            </p>

            <p style="margin:5px 0;">
                <strong>Vendor:</strong>
                {{ $order->dokan->company_name ?? 'Vendor' }}
            </p>

            <p style="margin:5px 0;">
                <strong>Payment Method:</strong>
                {{ strtoupper($order->payment_method) }}
            </p>

            <p style="margin:5px 0;">
                <strong>Status:</strong>
                {{ ucfirst($order->status) }}  
            </p>

        </div>

        <!-- Products -->
        <h3 style="color:#1a2a6c;">
            Your Products
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

        <!-- Total -->
        <div style="text-align:right; margin-top:20px;">

            <h2 style="color:#1a2a6c;">
                Total: Rs. {{ number_format($order->total_amount, 2) }}
            </h2>

        </div>

        <!-- Shipping -->
        @if($order->shippingAddress)

            <div style="background:#f8f9fa; padding:15px; border-radius:8px; margin-top:20px;">

                <h3 style="margin-top:0; color:#1a2a6c;">
                    Shipping Address
                </h3>

                <p style="margin:5px 0;">
                    {{ $order->shippingAddress->address ?? '' }}
                </p>

                @if(!empty($order->shippingAddress->city))
                    <p style="margin:5px 0;">
                        {{ $order->shippingAddress->city }}
                    </p>
                @endif

                @if(!empty($order->shippingAddress->phone))
                    <p style="margin:5px 0;">
                        Phone: {{ $order->shippingAddress->phone }}
                    </p>
                @endif

            </div>

        @endif

        <p style="margin-top:30px; color:#555;">
            We will notify you when your order status changes.
        </p>

    </div>

    <!-- Footer -->
    <div style="background:#f1f1f1; padding:20px; text-align:center; color:#777;">

        <p style="margin:0;">
            © {{ date('Y') }} Empire Innovation
        </p>

    </div>

</div>

</body>
</html>