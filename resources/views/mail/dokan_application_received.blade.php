<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Received</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, Helvetica, sans-serif;">

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

            <!-- LOGO -->
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
                    style="
                        width:100px;
                        height:100px;
                        object-fit:contain;
                        display:block;
                    "
                >

            </div>

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
            ">
                🎉 Application Received!
            </h2>


            <p style="
                color:#555;
                line-height:1.6;
            ">
                Dear <strong>{{ $dokan->company_name }}</strong>,
            </p>


            <p style="
                color:#555;
                line-height:1.6;
            ">
                Thank you for registering as a vendor with
                <strong>Empireinnovation PVT. LTD</strong>.
            </p>


            <p style="
                color:#555;
                line-height:1.6;
            ">
                We have successfully received your vendor application.
                Our team will review your application and notify you once
                a decision has been made.
            </p>


            <!-- STATUS BOX -->
            <div style="
                background:#fff8e6;
                border-left:4px solid #c9a84c;
                padding:18px;
                margin:25px 0;
                border-radius:6px;
            ">

                <strong style="color:#1a2a6c;">
                    Application Status:
                </strong>

                <div style="
                    margin-top:8px;
                    color:#d97706;
                    font-weight:bold;
                ">
                    ⏳ Pending Review
                </div>

            </div>


            <!-- APPLICATION DETAILS -->
            <div style="
                background:#f8f9fa;
                padding:20px;
                border-radius:8px;
            ">

                <h3 style="
                    margin-top:0;
                    color:#1a2a6c;
                ">
                    📋 Application Details
                </h3>


                <p style="color:#555;">
                    <strong>Company:</strong>
                    {{ $dokan->company_name }}
                </p>


                <p style="color:#555;">
                    <strong>Email:</strong>
                    {{ $dokan->email }}
                </p>


                <p style="color:#555;">
                    <strong>Registration No:</strong>
                    {{ $dokan->reg_no }}
                </p>


                <p style="color:#555;">
                    <strong>Contact Number:</strong>
                    {{ $dokan->contact_number }}
                </p>

            </div>


            <p style="
                color:#555;
                line-height:1.6;
                margin-top:25px;
            ">
                We appreciate your interest in joining our marketplace.
                We will contact you once your application has been reviewed.
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