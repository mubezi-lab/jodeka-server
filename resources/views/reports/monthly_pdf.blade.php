<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .section {
            width: 100%;
            margin-bottom: 15px;
        }

        .box {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        .label {
            background: #eee;
            font-weight: bold;
        }

        .main {
            margin-top: 10px;
        }

        .main th {
            background: #2f6f9f;
            color: white;
            padding: 6px;
            border: 1px solid #000;
        }

        .main td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 11px;
        }
    </style>
</head>
<body>

<!-- 🔥 1. HEADER -->
<h1>
    Monthly Report For {{ \Carbon\Carbon::parse($from)->format('F, Y') }}
</h1>

<!-- 🔥 2. TOP SUMMARY -->
<div class="section">

    <!-- LEFT BOX -->
    <div class="box">
        <table>
            <tr>
                <td class="label">Business Branch</td>
                <td>{{ $business ?? 'Main Branch' }}</td>
            </tr>
            <tr>
                <td class="label">Seller</td>
                <td>{{ $seller ?? 'Admin' }}</td>
            </tr>
            <tr>
                <td class="label">Balance Due</td>
                <td>{{ number_format($balance_due ?? 0) }}</td>
            </tr>
            <tr>
                <td class="label">Cash on hand</td>
                <td>{{ number_format($cash ?? 0) }}</td>
            </tr>
            <tr>
                <td class="label">Loss</td>
                <td>{{ number_format($loss ?? 0) }}</td>
            </tr>
        </table>
    </div>

    <!-- RIGHT BOX -->
    <div class="box">
        <table>
            <tr>
                <td class="label">Last Capital</td>
                <td>{{ number_format($last_capital ?? 0) }}</td>
            </tr>
            <tr>
                <td class="label">Current Capital</td>
                <td>{{ number_format($current_capital ?? 0) }}</td>
            </tr>
            <tr>
                <td class="label">Total Sales</td>
                <td>{{ number_format($totalSales ?? 0) }}</td>
            </tr>
            <tr>
                <td class="label">Out Source</td>
                <td>{{ number_format($out_source ?? 0) }}</td>
            </tr>
            <tr>
                <td class="label">Total Purchase</td>
                <td>{{ number_format($totalPurchase ?? 0) }}</td>
            </tr>
            <tr>
                <td class="label">Total Expenditure</td>
                <td>{{ number_format($total_expenditure ?? 0) }}</td>
            </tr>
        </table>
    </div>

</div>

<p style="margin-bottom: 10px;">
    <strong>Auditing Period:</strong>
    {{ \Carbon\Carbon::parse($from)->format('d M Y') }}
    To
    {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
</p>

<!-- 🔥 3. MAIN TABLE -->
<table class="main">
    <thead>
        <tr>
            <th>Product Name</th>
            <th>Open stock</th>
            <th>Purchase</th>
            <th>Total</th>
            <th>Closing Stock</th>
            <th>Sold</th>
            <th>Price</th>
            <th>Total Price</th>
        </tr>
    </thead>

    <tbody>
        @foreach($report as $r)
        <tr>
            <td>{{ $r['name'] }}</td>
            <td>{{ number_format($r['opening'], 2) }}</td>
            <td>{{ $r['purchase'] }}</td>
            <td>{{ number_format($r['total'], 2) }}</td>
            <td>{{ number_format($r['closing'], 2) }}</td>
            <td>{{ number_format($r['sold'], 2) }}</td>
            <td>{{ number_format($r['price']) }}</td>
            <td>{{ number_format($r['total_price']) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- 🔥 FOOTER -->
<div class="footer">
    Page 1
</div>

</body>
</html>