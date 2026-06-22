<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: dejavusans, sans-serif;
            font-size: 13px;
            background-color: #BDD8E9;
            color: #001D39;
            padding: 30px;
        }

        .header {
            background-color: #001D39;
            color: #BDD8E9;
            text-align: center;
            padding: 20px 24px 16px;
            border-radius: 10px 10px 0 0;
        }

        .header h2 {
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 0.3px;
            margin: 0;
        }

        .header .ar {
            font-size: 13px;
            color: #7BBDE8;
            display: block;
            margin-top: 4px;
        }

        .period-bar {
            background-color: #0A4174;
            color: #BDD8E9;
            text-align: center;
            padding: 9px 16px;
            font-size: 12px;
            margin-bottom: 22px;
            border-radius: 0 0 10px 10px;
        }

        .period-bar strong {
            color: #7BBDE8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background-color: #0A4174;
            color: #BDD8E9;
        }

        thead th {
            padding: 11px 14px;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            border: none;
        }

        thead th .ar {
            display: block;
            color: #7BBDE8;
            font-size: 10px;
            font-weight: normal;
            margin-top: 2px;
        }

        tbody tr:nth-child(odd)  { background-color: #ffffff; }
        tbody tr:nth-child(even) { background-color: #EAF4FB; }

        tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid #D6EAF5;
            color: #001D39;
        }

        tbody td:last-child {
            text-align: right;
            font-weight: bold;
            color: #0A4174;
        }

        tbody td .ar {
            display: block;
            color: #49769F;
            font-size: 10px;
            margin-top: 2px;
        }

        .summary-wrap {
            margin-top: 22px;
            display: flex;
            justify-content: flex-end;
        }

        .summary {
            width: 50%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
        }

        .summary tr:nth-child(1) td:first-child { background-color: #49769F; color: #fff; }
        .summary tr:nth-child(2) td:first-child { background-color: #4E8EA2; color: #fff; }
        .summary tr:nth-child(3) td:first-child { background-color: #0A4174; color: #BDD8E9; }

        .summary td {
            padding: 11px 16px;
            border-bottom: 1px solid #D6EAF5;
            font-weight: 500;
            font-size: 13px;
        }

        .summary td .ar {
            display: block;
            font-size: 10px;
            opacity: 0.75;
            margin-top: 2px;
        }

        .summary td:last-child {
            text-align: right;
            background-color: #EAF4FB;
            color: #001D39;
        }

        .summary tr:last-child td:last-child {
            background-color: #001D39;
            color: #BDD8E9;
            font-size: 14px;
        }

    </style>

</head>

<body>

    <div class="header">
        <h2>
            Tax Declaration Report
            <span class="ar">تقرير الإقرار الضريبي</span>
        </h2>
    </div>

    <div class="period-bar">
        <strong>Period | الفترة</strong>
        &nbsp;:&nbsp;
        <span dir="ltr">
            {{ \Carbon\Carbon::parse($data['period']['from'])->format('d-m-Y') }}
            &nbsp;–&nbsp;
            {{ \Carbon\Carbon::parse($data['period']['to'])->format('d-m-Y') }}
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th width="65%">
                    Account
                    <span class="ar">الحساب</span>
                </th>
                <th width="35%">
                    Value
                    <span class="ar">القيمة</span>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['rows'] as $row)
            <tr>
                <td>
                    @if($row['account'] == "Output Tax")
                        Output Tax
                        <span class="ar">ضريبة المخرجات</span>
                    @elseif($row['account'] == "Input Tax")
                        Input Tax
                        <span class="ar">ضريبة المدخلات</span>
                    @else
                        {{ $row['account'] }}
                    @endif
                </td>
                <td>{{ number_format($row['value'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-wrap">
        <table class="summary">
            <tr>
                <td>
                    Output Tax
                    <span class="ar">ضريبة المخرجات</span>
                </td>
                <td>{{ number_format($data['output_tax'], 2) }}</td>
            </tr>
            <tr>
                <td>
                    Input Tax
                    <span class="ar">ضريبة المدخلات</span>
                </td>
                <td>{{ number_format($data['input_tax'], 2) }}</td>
            </tr>
            <tr>
                <td>
                    Tax Due
                    <span class="ar">الضريبة المستحقة</span>
                </td>
                <td>{{ number_format($data['tax_due'], 2) }}</td>
            </tr>
        </table>
    </div>

</body>

</html>