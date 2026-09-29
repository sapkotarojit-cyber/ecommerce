<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Return & Refund Status Updated - EmpireInnovation
    </title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f4f6f8;
    font-family: Arial, Helvetica, sans-serif;
    color: #1f2937;
">

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="background-color: #f4f6f8; padding: 30px 15px;"
>
    <tr>
        <td align="center">

            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    max-width: 650px;
                    background-color: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    border: 1px solid #e5e7eb;
                "
            >

                {{-- Header --}}
                <tr>
                    <td
                        style="
                            background-color: #111827;
                            padding: 24px 30px;
                            text-align: center;
                        "
                    >
                        <h1
                            style="
                                margin: 0;
                                color: #ffffff;
                                font-size: 24px;
                                font-weight: 700;
                            "
                        >
                            EmpireInnovation
                        </h1>

                        <p
                            style="
                                margin: 8px 0 0;
                                color: #d1d5db;
                                font-size: 14px;
                            "
                        >
                            Return & Refund Status Update
                        </p>
                    </td>
                </tr>

                {{-- Content --}}
                <tr>
                    <td style="padding: 30px;">

                        <h2
                            style="
                                margin: 0 0 15px;
                                font-size: 21px;
                                color: #111827;
                            "
                        >
                            Hello {{ $returnRequest->user?->name ?? 'Customer' }},
                        </h2>

                        <p
                            style="
                                margin: 0 0 20px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #4b5563;
                            "
                        >
                            There has been an update to your return request.
                            Below are the latest return and refund statuses.
                        </p>

                        {{-- Order Information --}}
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin-bottom: 25px;
                                border: 1px solid #e5e7eb;
                                border-radius: 8px;
                            "
                        >
                            <tr>
                                <td
                                    colspan="2"
                                    style="
                                        padding: 14px 16px;
                                        background-color: #f9fafb;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >
                                    <strong style="color: #111827;">
                                        Order Information
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td
                                    width="45%"
                                    style="
                                        padding: 12px 16px;
                                        color: #6b7280;
                                        font-size: 14px;
                                    "
                                >
                                    Order Number
                                </td>

                                <td
                                    style="
                                        padding: 12px 16px;
                                        color: #111827;
                                        font-size: 14px;
                                        font-weight: 600;
                                    "
                                >
                                    #{{ $returnRequest->order?->id ?? $returnRequest->order_id }}
                                </td>
                            </tr>

                            @if($returnRequest->order?->tracking_number)
                                <tr>
                                    <td
                                        style="
                                            padding: 12px 16px;
                                            color: #6b7280;
                                            font-size: 14px;
                                        "
                                    >
                                        Tracking Number
                                    </td>

                                    <td
                                        style="
                                            padding: 12px 16px;
                                            color: #111827;
                                            font-size: 14px;
                                            font-weight: 600;
                                        "
                                    >
                                        {{ $returnRequest->order->tracking_number }}
                                    </td>
                                </tr>
                            @endif
                        </table>

                        {{-- Return Status --}}
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin-bottom: 20px;
                                border: 1px solid #e5e7eb;
                                border-radius: 8px;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 15px 16px;
                                        background-color: #f9fafb;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >
                                    <strong style="color: #111827;">
                                        Return Status
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 18px 16px;">

                                    <p
                                        style="
                                            margin: 0 0 8px;
                                            color: #6b7280;
                                            font-size: 13px;
                                        "
                                    >
                                        Previous Status
                                    </p>

                                    <p
                                        style="
                                            margin: 0 0 15px;
                                            color: #6b7280;
                                            font-size: 15px;
                                        "
                                    >
                                        {{ ucfirst($oldReturnStatus ?: 'N/A') }}
                                    </p>

                                    <p
                                        style="
                                            margin: 0 0 8px;
                                            color: #6b7280;
                                            font-size: 13px;
                                        "
                                    >
                                        Current Status
                                    </p>

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 7px 13px;
                                            background-color: #111827;
                                            color: #ffffff;
                                            border-radius: 5px;
                                            font-size: 14px;
                                            font-weight: 600;
                                        "
                                    >
                                        {{ ucfirst($newReturnStatus ?: 'N/A') }}
                                    </span>

                                </td>
                            </tr>
                        </table>

                        {{-- Refund Status --}}
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin-bottom: 25px;
                                border: 1px solid #e5e7eb;
                                border-radius: 8px;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 15px 16px;
                                        background-color: #f9fafb;
                                        border-bottom: 1px solid #e5e7eb;
                                    "
                                >
                                    <strong style="color: #111827;">
                                        Refund Status
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 18px 16px;">

                                    <p
                                        style="
                                            margin: 0 0 8px;
                                            color: #6b7280;
                                            font-size: 13px;
                                        "
                                    >
                                        Previous Status
                                    </p>

                                    <p
                                        style="
                                            margin: 0 0 15px;
                                            color: #6b7280;
                                            font-size: 15px;
                                        "
                                    >
                                        {{ ucfirst($oldRefundStatus ?: 'N/A') }}
                                    </p>

                                    <p
                                        style="
                                            margin: 0 0 8px;
                                            color: #6b7280;
                                            font-size: 13px;
                                        "
                                    >
                                        Current Status
                                    </p>

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 7px 13px;
                                            background-color: #111827;
                                            color: #ffffff;
                                            border-radius: 5px;
                                            font-size: 14px;
                                            font-weight: 600;
                                        "
                                    >
                                        {{ ucfirst($newRefundStatus ?: 'N/A') }}
                                    </span>

                                </td>
                            </tr>
                        </table>

                        {{-- Refund Amount --}}
                        @if($returnRequest->refund_amount !== null)
                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    margin-bottom: 25px;
                                    background-color: #f9fafb;
                                    border-radius: 8px;
                                "
                            >
                                <tr>
                                    <td style="padding: 18px 16px;">

                                        <p
                                            style="
                                                margin: 0 0 5px;
                                                color: #6b7280;
                                                font-size: 13px;
                                            "
                                        >
                                            Refund Amount
                                        </p>

                                        <p
                                            style="
                                                margin: 0;
                                                color: #111827;
                                                font-size: 20px;
                                                font-weight: 700;
                                            "
                                        >
                                            NPR {{ number_format((float) $returnRequest->refund_amount, 2) }}
                                        </p>

                                    </td>
                                </tr>
                            </table>
                        @endif

                        <p
                            style="
                                margin: 0;
                                font-size: 14px;
                                line-height: 1.7;
                                color: #6b7280;
                            "
                        >
                            If you have any questions regarding your return or
                            refund, please contact EmpireInnovation support.
                        </p>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td
                        style="
                            padding: 20px 30px;
                            background-color: #f9fafb;
                            border-top: 1px solid #e5e7eb;
                            text-align: center;
                        "
                    >
                        <p
                            style="
                                margin: 0;
                                color: #6b7280;
                                font-size: 12px;
                                line-height: 1.6;
                            "
                        >
                            This is an automated email from EmpireInnovation.
                            Please do not reply directly to this email.
                        </p>

                        <p
                            style="
                                margin: 8px 0 0;
                                color: #9ca3af;
                                font-size: 12px;
                            "
                        >
                            &copy; {{ date('Y') }} EmpireInnovation
                        </p>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>