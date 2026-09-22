<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Application Rejected - Empireinnovation</title>
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
                display:flex;
                width:180px;
                height:140px;
                margin:0 auto;
                align-items:center;
                justify-content:center;
            ">
                <img
                    src="{{ $message->embed(public_path('images/logo5.png')) }}"
                    alt="Empire Innovation"
                    width="160"
                    style="
                        width:160px;
                        height:auto;
                        display:block;
                        object-fit:contain;
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
            Vendor Application Update
        </h2>


        <p style="
            color:#555;
            line-height:1.6;
            margin:10px 0;
        ">
            Dear <strong>{{ $dokan->company_name }}</strong>,
        </p>


        <p style="
            color:#555;
            line-height:1.6;
            margin:10px 0;
        ">
            Thank you for your interest in becoming a vendor with
            <strong>Empireinnovation PVT. LTD</strong>.
        </p>


        <p style="
            color:#555;
            line-height:1.6;
            margin:10px 0;
        ">
            After carefully reviewing your vendor application, we regret
            to inform you that your application has not been approved at
            this time.
        </p>


        <!-- STATUS BOX -->
        <div style="
            background:#fff4f4;
            border-left:4px solid #dc3545;
            padding:18px;
            margin:25px 0;
            border-radius:6px;
        ">

            <strong style="color:#1a2a6c;">
                Application Status:
            </strong>

            <div style="
                margin-top:8px;
                color:#dc3545;
                font-weight:bold;
            ">
                ✕ Application Rejected
            </div>

        </div>


        <!-- REJECTION REASON -->
        <div style="
            background:#f8f9fa;
            padding:20px;
            border-radius:8px;
            border:1px solid #e5e7eb;
        ">

            <h3 style="
                margin-top:0;
                color:#1a2a6c;
                font-size:17px;
            ">
                📋 Reason for Rejection
            </h3>

            <p style="
                margin-bottom:0;
                color:#555;
                line-height:1.6;
            ">
                {{ $comment }}
            </p>

        </div>


        <!-- APPLICATION DETAILS -->
        <div style="
            background:#f8f9fa;
            padding:20px;
            border-radius:8px;
            margin-top:20px;
        ">

            <h3 style="
                margin-top:0;
                color:#1a2a6c;
                font-size:17px;
            ">
                🏢 Application Details
            </h3>


            <p style="
                color:#555;
                margin:10px 0;
            ">
                <strong>Company:</strong>
                {{ $dokan->company_name }}
            </p>


            <p style="
                color:#555;
                margin:10px 0;
            ">
                <strong>Email:</strong>
                {{ $dokan->email }}
            </p>


            <p style="
                color:#555;
                margin:10px 0;
            ">
                <strong>Registration No:</strong>
                {{ $dokan->reg_no }}
            </p>


            <p style="
                color:#555;
                margin:10px 0;
            ">
                <strong>Contact Number:</strong>
                {{ $dokan->contact_number }}
            </p>

        </div>


        <p style="
            color:#555;
            line-height:1.6;
            margin-top:25px;
        ">
            If you believe this decision was made in error or you would
            like further information regarding your application, please
            contact our support team.
        </p>


        <p style="
            color:#555;
            line-height:1.6;
        ">
            We appreciate the time and effort you took to apply and thank
            you for your interest in joining our marketplace.
        </p>


        <p style="
            color:#555;
            line-height:1.6;
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

    </div>

</div>

</body>
</html>