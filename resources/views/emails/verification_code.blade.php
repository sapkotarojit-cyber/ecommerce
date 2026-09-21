<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Empireinnovation</title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f4f6f9;
    font-family:Arial, Helvetica, sans-serif;
">

<div style="
    max-width:600px;
    margin:40px auto;
    background:#ffffff;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
">

    <!-- HEADER -->
    <div style="
        background:#1c253f;
        padding:25px 20px 30px;
        text-align:center;
        color:#1c253f;
    ">

        <!-- LOGO WHITE BOX -->
        <div style="
            background:#1c253f;
            width:120px;
            height:120px;
            margin:0 auto;
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
        ">

            <img
                src="{{ $message->embed(public_path('images/logo5.png')) }}"
                alt="Empire Innovation"
                width="100"
                height="100"
                style="
                    width:100px;
                    height:100px;
                    object-fit:contain;
                    display:block;
                    margin:0 auto;
                "
            >

        </div>


        <!-- MARKETPLACE TEXT -->
        <p style="
            margin:12px 0 0;
            color:#c9a84c;
            font-size:15px;
            font-weight:bold;
        ">
            Multi-Vendor Marketplace
        </p>

    </div>


    <!-- CONTENT -->
    <div style="padding:30px;">

        <h2 style="
            color:#1a2a6c;
            margin-top:0;
            font-size:21px;
        ">
            ✉️ Verify Your Email Address
        </h2>


        <p style="
            color:#555;
            line-height:1.6;
            margin:10px 0;
        ">
            Thank you for registering with
            <strong>Empireinnovation</strong>.
        </p>


        <p style="
            color:#555;
            line-height:1.6;
            margin:10px 0;
        ">
            Please use the following 6-digit verification code to
            complete your registration:
        </p>


        <!-- VERIFICATION CODE -->
        <div style="
            background:#f8f7f4;
            border-left:4px solid #c9a84c;
            padding:22px;
            margin:25px 0;
            border-radius:6px;
            text-align:center;
        ">

            <p style="
                margin:0 0 10px;
                color:#6b7280;
                font-size:13px;
            ">
                Your Verification Code
            </p>

            <div style="
                font-size:32px;
                font-weight:700;
                letter-spacing:8px;
                color:#1a2a6c;
                font-family:'Courier New', Courier, monospace;
            ">
                {{ $code }}
            </div>

        </div>


        <!-- ALERT -->
        <div style="
            background:#fff8e6;
            border-left:4px solid #c9a84c;
            padding:16px;
            border-radius:6px;
            margin:20px 0;
        ">

            <strong style="color:#856404;">
                ⏰ Notice:
            </strong>

            <span style="
                color:#856404;
                font-size:14px;
                line-height:1.5;
            ">
                This code will expire in 10 minutes.
                If you did not request this verification code,
                please ignore this email.
            </span>

        </div>


        <p style="
            color:#777;
            line-height:1.6;
            margin-top:25px;
        ">
            Best regards,<br>

            <strong style="color:#1a2a6c;">
                Empireinnovation PVT. LTD
            </strong>
        </p>

    </div>


    <!-- FOOTER -->
    <div style="
        background:#f8f9fa;
        padding:20px;
        text-align:center;
        border-top:1px solid #e5e7eb;
    ">

        <p style="
            margin:0;
            color:#1a2a6c;
            font-weight:600;
        ">
            Empireinnovation
            <span style="color:#c9a84c;">
                PVT. LTD
            </span>
        </p>


        <p style="
            margin:8px 0 0;
            color:#888;
            font-size:12px;
        ">
            Multi-Vendor Marketplace
        </p>


        <p style="
            margin:8px 0 0;
            color:#888;
            font-size:12px;
        ">
            © {{ date('Y') }} Empireinnovation PVT. LTD.
            All rights reserved.
        </p>


        <p style="
            margin:8px 0 0;
            font-size:12px;
        ">
            <a
                href="{{ config('app.url') }}"
                style="
                    color:#1a2a6c;
                    text-decoration:none;
                "
            >
                Visit our website
            </a>

            &nbsp;|&nbsp;

            <a
                href="mailto:empireinnovation2025@gmail.com"
                style="
                    color:#1a2a6c;
                    text-decoration:none;
                "
            >
                Contact Support
            </a>
        </p>

    </div>

</div>

</body>
</html>