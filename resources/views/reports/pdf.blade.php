<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Financial Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .header {
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
        }

        .subtitle {
            color: #666;
            margin-top: 5px;
        }

        .summary {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        .summary-title {
            font-size: 11px;
            color: #666;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #f2f2f2;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .income {
            color: green;
            font-weight: bold;
        }

        .expense {
            color: red;
            font-weight: bold;
        }
    </style>

</head>

<body>

    {{-- HEADER --}}
    <div class="header">

        <div class="title">

            JODEKA BUSINESS REPORT

        </div>

        <div class="subtitle">

            From:
            {{ $from }}

            -

            To:
            {{ $to }}

        </div>

    </div>

    {{-- SUMMARY --}}
    <div class="summary">

        <table class="summary-table">

            <tr>

                <td>

                    <div class="summary-title">

                        Total Income

                    </div>

                    <div class="summary-value">

                        TZS {{ number_format($totalIncome, 2) }}

                    </div>

                </td>

                <td>

                    <div class="summary-title">

                        Total Expenses

                    </div>

                    <div class="summary-value">

                        TZS {{ number_format($totalExpenses, 2) }}

                    </div>

                </td>

                <td>

                    <div class="summary-title">

                        Net Profit

                    </div>

                    <div class="summary-value">

                        TZS {{ number_format($netProfit, 2) }}

                    </div>

                </td>

            </tr>

        </table>

    </div>

    {{-- TRANSACTIONS --}}
    <table>

        <thead>

            <tr>

                <th>Date</th>
                <th>Type</th>
                <th>Business</th>
                <th>Category</th>
                <th>Payment</th>
                <th>Amount</th>
                <th>Description</th>

            </tr>

        </thead>

        <tbody>

            @forelse($transactions as $transaction)

                <tr>

                    <td>

                        {{ $transaction->transaction_date }}

                    </td>

                    <td>

                        {{ ucfirst($transaction->type) }}

                    </td>

                    <td>

                        {{ $transaction->business->name ?? '-' }}

                    </td>

                    <td>

                        {{ $transaction->category }}

                    </td>

                    <td>

                        {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}

                    </td>

                    <td class="{{ $transaction->type }}">

                        TZS {{ number_format($transaction->amount, 2) }}

                    </td>

                    <td>

                        {{ $transaction->description ?? '-' }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7">

                        No transactions found

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</body>

</html>