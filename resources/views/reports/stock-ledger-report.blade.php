<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: dejavusans;
            font-size: 11px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
        }

        .no-data {
            text-align: center;
            margin-top: 40px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<h2>Stock Ledger Report</h2>

@if($rows->isEmpty())
    <div class="no-data">No Stock Ledger Records Found</div>
@else
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Warehouse</th>
                <th>Transaction Type</th>
                <th>In Qty</th>
                <th>Out Qty</th>
                <th>Balance Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['item_code'] }}</td>
                    <td>{{ $row['item_name'] }}</td>
                    <td>{{ $row['warehouse'] }}</td>
                    <td>{{ $row['transaction_type'] }}</td>
                    <td>{{ number_format($row['in_qty'], 2) }}</td>
                    <td>{{ number_format($row['out_qty'], 2) }}</td>
                    <td>{{ number_format($row['balance_qty'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

</body>
</html>