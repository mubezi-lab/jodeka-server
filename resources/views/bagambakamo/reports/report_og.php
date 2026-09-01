<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .center {
            text-align: center;
        }

        .underline {
            text-decoration: underline;
        }

        .spacer {
            height: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #f1f1f1;
        }

        .right {
            text-align: right;
        }

        .center-text {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .summary-table {
            width: 45%;
            margin-top: 10px;
        }

        .total-row {
            background: #eaeaea;
            font-weight: bold;
            border-top: 2px solid #000;
        }

        .danger {
            color: red;
        }

        .success {
            color: green;
        }

        .row-alt {
            background: #fafafa;
        }

        .note {
            margin-top: 10px;
            font-size: 11px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <h2>BAGAMBAKAMO FINANCIAL REPORT (M-KOBA FUND)</h2>

    <p class="center underline">
        Report generated on {{ $reportDate->format('F d, Y \a\t h:i A') }}
    </p>

    <div class="spacer"></div>

    <!-- SUMMARY -->
    <table class="summary-table">
        <tr>
            <td><strong>Total Expected</strong></td>
            <td class="right">{{ number_format($expected) }}</td>
        </tr>
        <tr>
            <td><strong>Total Collected</strong></td>
            <td class="right">{{ number_format($collected) }}</td>
        </tr>
        <tr>
            <td><strong>Total Events Contribution</strong></td>
            <td class="right">{{ number_format($msiba) }}</td>
        </tr>
        <tr>
            <td><strong>Remaining Balance</strong></td>
            <td class="right">{{ number_format($remaining) }}</td>
        </tr>
        <tr>
            <td><strong>Excess Amount</strong></td>
            <td class="right">{{ number_format($excess) }}</td>
        </tr>
        <tr>
            <td><strong>Total Debt</strong></td>
            <td class="right danger">
                {{ number_format($totalDebt) }}
            </td>
        </tr>
    </table>

    <!-- MAIN TABLE -->
    <table>
        <thead>
            <tr>
                <th class="center-text">#</th>
                <th>Full Name</th>
                <th>Phone</th>
                <th class="right">Expected</th>
                <th class="right">Events</th>
                <th class="right">Total</th>
                <th class="right">Total Paid</th>
                <th class="right">Balance</th>
            </tr>
        </thead>

        <tbody>
            @foreach($members as $index => $member)
                <tr class="{{ $loop->even ? 'row-alt' : '' }}">

                    <td class="center-text">{{ $index + 1 }}</td>

                    <td>{{ $member->full_name }}</td>

                    <td>{{ $member->phone }}</td>

                    <td class="right">{{ number_format($member->expected_amount) }}</td>

                    <td class="right">{{ number_format($member->total_events) }}</td>

                    <td class="right bold">
                        {{ number_format($member->expected_amount + $member->total_events) }}
                    </td>

                    <td class="right">
                        {{ number_format($member->total_paid) }}
                    </td>

                    <td class="right bold {{ $member->balance_amount > 0 ? 'danger' : 'success' }}">
                        {{ number_format($member->balance_amount) }}
                    </td>

                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="center-text">TOTAL</td>

                <td class="right">{{ number_format($sumExpected) }}</td>
                <td class="right">{{ number_format($sumEvents) }}</td>
                <td class="right">{{ number_format($sumTotal) }}</td>
                <td class="right">{{ number_format($sumPaid) }}</td>

                <td class="right {{ $sumBalance > 0 ? 'danger' : 'success' }}">
                    {{ number_format($sumBalance) }}
                </td>
            </tr>
        </tfoot>

    </table>

    <div class="note">
        <em>
            Note: Includes {{ number_format($lowMembersTotal) }} from removed members.
        </em>
    </div>

</body>

</html>