<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokan Approval - Empireinnovation</title>
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
                font-size:22px;
            ">
                🎉 Congratulations! Your Vendor Application is Approved
            </h2>


            <p style="
                color:#555;
                line-height:1.6;
                margin:10px 0;
            ">
                Dear
                <strong>{{ $data['company_name'] ?? 'Vendor' }}</strong>,
            </p>


            <p style="
                color:#555;
                line-height:1.6;
                margin:10px 0;
            ">
                We are pleased to inform you that your vendor application has been
                <strong>approved</strong> by our team.
                You can now start selling your products on our marketplace.
            </p>


            <!-- IMPORTANT ALERT -->
            <div style="
                background:#fff8e6;
                border-left:4px solid #c9a84c;
                padding:18px;
                margin:20px 0;
                border-radius:6px;
            ">

                <strong style="color:#1a2a6c;">
                    📌 Important:
                </strong>

                <p style="
                    color:#555;
                    margin:8px 0 0;
                    line-height:1.5;
                ">
                    Please save your login credentials securely.
                    You will need them to access your vendor dashboard.
                </p>

            </div>


            <!-- CREDENTIALS BOX -->
            <div style="
                background:#f8f9fa;
                padding:20px;
                margin:20px 0;
                border-radius:8px;
            ">

                <h3 style="
                    color:#1a2a6c;
                    margin-top:0;
                    font-size:17px;
                ">
                    🔑 Your Login Credentials
                </h3>


                <p style="
                    color:#555;
                    line-height:1.6;
                    margin:10px 0;
                ">
                    <strong>Email:</strong>
                    <span style="color:#333;">
                        {{ $data['email'] ?? 'N/A' }}
                    </span>
                </p>


                <p style="
                    color:#555;
                    line-height:1.6;
                    margin:10px 0;
                ">
                    <strong>Password:</strong>

                    <span style="
                        background:#e8e8e8;
                        padding:4px 8px;
                        border-radius:4px;
                        font-family:monospace;
                        color:#333;
                    ">
                        {{ $password ?? 'N/A' }}
                    </span>
                </p>


                <p style="
                    font-size:12px;
                    color:#888;
                    margin-top:15px;
                    margin-bottom:0;
                ">
                    ℹ️ We recommend changing your password after your first login.
                </p>

            </div>


            <!-- LOGIN BUTTON -->
            <div style="
                text-align:center;
                margin:25px 0;
            ">

                <a
                    href="{{ config('app.url') }}/vendor/login"
                    style="
                        display:inline-block;
                        background:#c9a84c;
                        color:#1c253f;
                        padding:13px 30px;
                        text-decoration:none;
                        border-radius:6px;
                        font-weight:bold;
                        font-size:14px;
                    "
                >
                    🚀 Login to Vendor Dashboard
                </a>

            </div>


            <p style="
                color:#777;
                font-size:14px;
                line-height:1.6;
                margin-top:20px;
            ">
                If you have any questions or need assistance,
                please don't hesitate to contact our support team.
            </p>


            <p style="
                color:#777;
                line-height:1.6;
                margin-top:20px;
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
                margin:5px 0;
                color:#1a2a6c;
                font-weight:600;
            ">
                Empireinnovation
                <span style="color:#c9a84c;">
                    PVT. LTD
                </span>
            </p>


            <p style="
                margin:5px 0;
                color:#888;
                font-size:12px;
            ">
                © {{ date('Y') }} All rights reserved.
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
                    href="mailto:support@empireinnovation.com"
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