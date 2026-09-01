<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Bagambakamo Financial Report</title>

    <style>
        @page {
            margin: 22px 25px 25px 25px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
            margin: 0;
            padding: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        .logo-wrapper {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo {
            width: 125px;
            height: auto;
        }

        /*
        |--------------------------------------------------------------------------
        | DIVIDER
        |--------------------------------------------------------------------------
        */

        .divider {
            border: 0;
            border-top: 1.5px solid #111827;
            margin: 6px 0 10px 0;
        }

        /*
        |--------------------------------------------------------------------------
        | REPORT HEADER
        |--------------------------------------------------------------------------
        */

        .report-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0;
        }

        .generated-date {
            text-align: center;
            margin-top: 4px;
            margin-bottom: 8px;
            font-size: 10px;
            color: #4b5563;
        }

        /*
        |--------------------------------------------------------------------------
        | SECTION TITLES
        |--------------------------------------------------------------------------
        */

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 4px;
        }

        /*
        |--------------------------------------------------------------------------
        | TABLE BASE
        |--------------------------------------------------------------------------
        */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #6b7280;
            padding: 5px 5px;
            vertical-align: middle;
        }

        th {
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | SUMMARY TABLE
        |--------------------------------------------------------------------------
        */

        .summary-table {
            margin-bottom: 12px;
        }

        .summary-table thead th {
            background: #dff2df;
        }

        .summary-table .number {
            width: 7%;
            text-align: center;
        }

        .summary-table .description {
            width: 58%;
            text-align: left;
        }

        .summary-table .amount {
            width: 35%;
            text-align: right;
        }

        .summary-table tbody td.amount {
            text-align: right;
        }

        /*
        |--------------------------------------------------------------------------
        | MEMBERS TABLE
        |--------------------------------------------------------------------------
        */

        .members-table {
            font-size: 8.8px;
        }

        .members-table thead th {
            background: #dbeefe;
            text-align: center;
        }

        .members-table .num {
            width: 4%;
            text-align: center;
        }

        .members-table .name {
            width: 20%;
        }

        .members-table .phone {
            width: 15%;
        }

        .members-table .money {
            width: 12%;
            text-align: right;
            white-space: nowrap;
        }

        .members-table td.money {
            text-align: right;
        }

        .members-table td.name {
            text-align: left;
        }

        .members-table td.phone {
            text-align: left;
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL ROW
        |--------------------------------------------------------------------------
        */

        .total-row td {
            background: #dbeefe;
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | BALANCE
        |--------------------------------------------------------------------------
        */

        .balance-paid {
            color: #15803d;
            font-weight: bold;
        }

        .balance-owing {
            color: #dc2626;
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | SUMMARY SPECIAL
        |--------------------------------------------------------------------------
        */

        .debt {
            color: #dc2626;
            font-weight: bold;
        }

        .bold {
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | NOTE
        |--------------------------------------------------------------------------
        */

        .note {
            margin-top: 7px;
            font-size: 9px;
            font-style: italic;
        }

        /*
        |--------------------------------------------------------------------------
        | PREPARED BY
        |--------------------------------------------------------------------------
        */

        .prepared {
            margin-top: 22px;
            width: 300px;
            page-break-inside: avoid;
        }

        .prepared-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 7px;
        }

        .prepared-table {
            width: 300px;
            border-collapse: collapse;
        }

        .prepared-table td {
            border: none;
            padding: 2px 0;
            font-size: 9px;
            vertical-align: middle;
        }

        .prepared-label {
            width: 65px;
            font-weight: bold;
        }

        /*
        |--------------------------------------------------------------------------
        | SIGNATURE
        |--------------------------------------------------------------------------
        */

        .signature-row td {
            padding-top: 4px;
            vertical-align: bottom;
        }

        .signature-image {
            width: 65px;
            height: auto;
            display: block;
            margin-left: 5px;
            margin-bottom: 0;
        }

        .signature-line {
            width: 100px;
            border-top: 1px solid #111827;
            margin-top: 0;
        }
    </style>

</head>

<body>


    {{-- ========================================================= --}}
    {{-- 1. LOGO --}}
    {{-- ========================================================= --}}

    <div class="logo-wrapper">

        <img src="{{ public_path('images/bagambakamo-logo.png') }}" class="logo" alt="Bagambakamo Logo">

    </div>


    {{-- ========================================================= --}}
    {{-- 2. LINE --}}
    {{-- ========================================================= --}}

    <hr class="divider">


    {{-- ========================================================= --}}
    {{-- 3. FINANCIAL REPORT --}}
    {{-- ========================================================= --}}

    <h1 class="report-title">
        FINANCIAL REPORT
    </h1>


    {{-- ========================================================= --}}
    {{-- 4. GENERATED ON --}}
    {{-- ========================================================= --}}

    <div class="generated-date">

        Generated on
        {{ $reportDate->format('F d, Y \a\t h:i A') }}

    </div>


    {{-- ========================================================= --}}
    {{-- 5. LINE --}}
    {{-- ========================================================= --}}

    <hr class="divider">


    {{-- ========================================================= --}}
    {{-- 6. SUMMARY --}}
    {{-- ========================================================= --}}

    <div class="section-title">
        1. SUMMARY
    </div>


    <table class="summary-table">

        <thead>

            <tr>

                <th class="number">
                    #
                </th>

                <th class="description">
                    DESCRIPTION
                </th>

                <th class="amount">
                    AMOUNT (TSH)
                </th>

            </tr>

        </thead>


        <tbody>

            <tr>

                <td class="number">
                    1
                </td>

                <td>
                    Total Expected
                </td>

                <td class="amount">
                    {{ number_format($expected) }}
                </td>

            </tr>


            <tr>

                <td class="number">
                    2
                </td>

                <td>
                    Total Collected
                </td>

                <td class="amount">
                    {{ number_format($collected) }}
                </td>

            </tr>


            <tr>

                <td class="number">
                    3
                </td>

                <td>
                    Total Events Contribution
                </td>

                <td class="amount">
                    {{ number_format($msiba) }}
                </td>

            </tr>


            <tr>

                <td class="number">
                    4
                </td>

                <td>
                    Remaining Balance
                </td>

                <td class="amount">
                    {{ number_format($remaining) }}
                </td>

            </tr>


            <tr>

                <td class="number">
                    5
                </td>

                <td>
                    Excess Amount
                </td>

                <td class="amount">
                    {{ number_format($excess) }}
                </td>

            </tr>


            <tr>

                <td class="number bold">
                    6
                </td>

                <td class="bold">
                    Total Debt
                </td>

                <td class="amount debt">
                    {{ number_format($totalDebt) }}
                </td>

            </tr>

        </tbody>

    </table>


    {{-- ========================================================= --}}
    {{-- 7. MEMBER DETAILS --}}
    {{-- ========================================================= --}}

    <div class="section-title">
        2. MEMBER DETAILS
    </div>


    <table class="members-table">

        <thead>

            <tr>

                <th class="num">
                    #
                </th>

                <th class="name">
                    Full Name
                </th>

                <th class="phone">
                    Phone
                </th>

                <th class="money">
                    Expected
                </th>

                <th class="money">
                    Events
                </th>

                <th class="money">
                    Total
                </th>

                <th class="money">
                    Total Paid
                </th>

                <th class="money">
                    Balance
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach($members as $index => $member)

                @php
                    $memberTotal =
                        $member->expected_amount
                        +
                        $member->total_events;
                @endphp


                <tr>

                    <td class="num">
                        {{ $index + 1 }}
                    </td>


                    <td class="name">
                        {{ $member->full_name }}
                    </td>


                    <td class="phone">
                        {{ $member->phone ?: '-' }}
                    </td>


                    <td class="money">
                        {{ number_format($member->expected_amount) }}
                    </td>


                    <td class="money">
                        {{ number_format($member->total_events) }}
                    </td>


                    <td class="money bold">
                        {{ number_format($memberTotal) }}
                    </td>


                    <td class="money">
                        {{ number_format($member->total_paid) }}
                    </td>


                    <td class="money">

                        @if($member->balance_amount > 0)

                            <span class="balance-owing">
                                {{ number_format($member->balance_amount) }}
                            </span>

                        @else

                            <span class="balance-paid">
                                0
                            </span>

                        @endif

                    </td>

                </tr>

            @endforeach


            {{-- TOTALS --}}
            <tr class="total-row">

                <td colspan="3" style="text-align: center;">

                    TOTAL

                </td>


                <td class="money">
                    {{ number_format($sumExpected) }}
                </td>


                <td class="money">
                    {{ number_format($sumEvents) }}
                </td>


                <td class="money">
                    {{ number_format($sumTotal) }}
                </td>


                <td class="money">
                    {{ number_format($sumPaid) }}
                </td>


                <td class="money">

                    <span class="{{ $sumBalance > 0 ? 'balance-owing' : 'balance-paid' }}">

                        {{ number_format($sumBalance) }}

                    </span>

                </td>

            </tr>

        </tbody>

    </table>


    {{-- ========================================================= --}}
    {{-- 8. NOTE --}}
    {{-- ========================================================= --}}

    <div class="note">

        <strong>
            Note:
        </strong>

        Includes
        {{ number_format($lowMembersTotal) }}
        from removed members.

    </div>


    {{-- ========================================================= --}}
    {{-- 9. PREPARED BY --}}
    {{-- ========================================================= --}}

    <div class="prepared">

        <div class="prepared-title">
            Prepared by:
        </div>

        <table class="prepared-table">

            <tr>

                <td class="prepared-label">
                    Name:
                </td>

                <td>
                    Jackson Kaika
                </td>

            </tr>


            <tr>

                <td class="prepared-label">
                    Position:
                </td>

                <td>
                    Treasurer
                </td>

            </tr>


            <tr class="signature-row">

                <td class="prepared-label">
                    Signature:
                </td>

                <td>

                    <img src="{{ public_path('images/jackson-signature.png') }}" class="signature-image"
                        alt="Jackson Kaika Signature">

                    <div class="signature-line"></div>

                </td>

            </tr>

        </table>

    </div>


</body>

</html>