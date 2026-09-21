<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Vendor Registration - Empireinnovation</title>
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
    background:#1c253f;
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

        <!-- NEW REQUEST BADGE -->
        <div style="
            display:inline-block;
            margin-top:14px;
            background:rgba(255,255,255,0.12);
            padding:6px 16px;
            border-radius:20px;
            font-size:12px;
            color:#ffffff;
            border:1px solid rgba(255,255,255,0.18);
        ">
            ⚡ New Vendor Request
        </div>

    </div>


    <!-- CONTENT -->
    <div style="padding:30px;">

        <h2 style="
            color:#1a2a6c;
            margin:0 0 5px;
            font-size:21px;
        ">
            Dear Admin,
        </h2>


        <p style="
            color:#6b7280;
            margin-top:5px;
            line-height:1.6;
        ">
            A new vendor has submitted a registration request.
        </p>


        <hr style="
            border:0;
            border-top:1px solid #e5e7eb;
            margin:20px 0;
        ">


        <!-- VENDOR DETAILS -->
        <h3 style="
            color:#1a2a6c;
            margin-bottom:12px;
            font-size:17px;
        ">
            📋 Vendor Details
        </h3>


        <table style="
            width:100%;
            border-collapse:collapse;
            font-size:14px;
        ">

            <tr>
                <td style="
                    padding:8px 0;
                    color:#6b7280;
                    font-weight:600;
                    width:40%;
                ">
                    Company Name:
                </td>

                <td style="
                    padding:8px 0;
                    color:#1f2937;
                    font-weight:500;
                ">
                    {{ $dokan->company_name ?? 'N/A' }}
                </td>
            </tr>


            <tr>
                <td style="
                    padding:8px 0;
                    color:#6b7280;
                    font-weight:600;
                ">
                    Contact Person:
                </td>

                <td style="
                    padding:8px 0;
                    color:#1f2937;
                    font-weight:500;
                ">
                    {{ $dokan->name ?? 'N/A' }}
                </td>
            </tr>


            <tr>
                <td style="
                    padding:8px 0;
                    color:#6b7280;
                    font-weight:600;
                ">
                    Email:
                </td>

                <td style="
                    padding:8px 0;
                    color:#1f2937;
                    font-weight:500;
                ">
                    {{ $dokan->email ?? 'N/A' }}
                </td>
            </tr>


            <tr>
                <td style="
                    padding:8px 0;
                    color:#6b7280;
                    font-weight:600;
                ">
                    Contact Number:
                </td>

                <td style="
                    padding:8px 0;
                    color:#1f2937;
                    font-weight:500;
                ">
                    {{ $dokan->contact_number ?? 'N/A' }}
                </td>
            </tr>


            <tr>
                <td style="
                    padding:8px 0;
                    color:#6b7280;
                    font-weight:600;
                ">
                    Registration No:
                </td>

                <td style="
                    padding:8px 0;
                    color:#1f2937;
                    font-weight:500;
                ">
                    {{ $dokan->reg_no ?? 'N/A' }}
                </td>
            </tr>


            <tr>
                <td style="
                    padding:8px 0;
                    color:#6b7280;
                    font-weight:600;
                ">
                    Status:
                </td>

                <td style="
                    padding:8px 0;
                    color:#d97706;
                    font-weight:600;
                ">
                    ⏳ Pending Review
                </td>
            </tr>

        </table>


        <!-- COMPANY LOGO -->
        @if(isset($dokan->logo) && $dokan->logo)

        <div style="
            background:#f9fafb;
            padding:16px;
            border-radius:8px;
            margin:20px 0;
            text-align:center;
            border:2px dashed #e5e7eb;
        ">

            <p style="
                margin:0;
                font-size:13px;
                color:#6b7280;
            ">
                Company Logo:
            </p>

            <img
                src="{{ asset('storage/' . $dokan->logo) }}"
                alt="Company Logo"
                style="
                    max-width:100px;
                    max-height:100px;
                    border-radius:8px;
                    margin-top:8px;
                "
            >

        </div>

        @endif


        <hr style="
            border:0;
            border-top:1px solid #e5e7eb;
            margin:20px 0;
        ">


        <!-- ACTION BUTTONS -->
        <div style="
            text-align:center;
            margin:20px 0;
        ">

            <a
                href="{{ config('app.url') }}/admin"
                style="
                    display:inline-block;
                    background:#c9a84c;
                    color:#1c253f;
                    padding:11px 24px;
                    border-radius:6px;
                    text-decoration:none;
                    font-weight:bold;
                    margin:5px;
                "
            >
                Review Application
            </a>

        </div>


        <p style="
            color:#6b7280;
            font-size:13px;
            line-height:1.6;
            margin-top:16px;
        ">
            <strong>Note:</strong>
            Please review the application and take appropriate action.
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
            color:#999;
            font-size:11px;
        ">
            This is an automated notification.
            Please do not reply to this email.
        </p>

    </div>

</div>

</body>
</html>