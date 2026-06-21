@php
    $isArabic = app()->getLocale() === 'ar';

    $t = [
        'title' => $isArabic ? 'تقرير ملخص دفعات المبيعات' : 'Sales Payment Summary Report',
        'from' => $isArabic ? 'من' : 'From',
        'to' => $isArabic ? 'إلى' : 'To',
        'date' => $isArabic ? 'التاريخ' : 'Date',
        'owner' => $isArabic ? 'مندوب المبيعات' : 'Owner',
        'payment_mode' => $isArabic ? 'طريقة الدفع' : 'Payment Mode',
        'sales_return' => $isArabic ? 'المبيعات والمرتجعات' : 'Sales & Return',
        'tax' => $isArabic ? 'الضريبة' : 'Tax',
        'payment' => $isArabic ? 'المدفوع' : 'Payment',
        'total' => $isArabic ? 'الإجمالي' : 'Total',
        'no_data' => $isArabic ? 'لا توجد بيانات' : 'No Data Available',
    ];
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: dejavusans;
            font-size: 11px;
            direction: {{ $isArabic ? 'rtl' : 'ltr' }};
        }

        h2 {
            text-align: center;
            margin-bottom: 8px;
        }

        .period {
            text-align: center;
            margin-bottom: 12px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        th {
            background: #eeeeee;
            font-weight: bold;
        }

        .total-row {
            background: #f3f3f3;
            font-weight: bold;
        }

        .no-data {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
        }
    </style>
</head>
<body>

<h2>{{ $t['title'] }}</h2>

<div class="period">
    {{ $t['from'] }}: {{ $fromDate }}
    &nbsp;&nbsp;&nbsp;
    {{ $t['to'] }}: {{ $toDate }}
</div>

@if(empty($rows))
    <div class="no-data">{{ $t['no_data'] }}</div>
@else
    <table>
        <thead>
        <tr>
            <th>{{ $t['date'] }}</th>
            <th>{{ $t['owner'] }}</th>
            <th>{{ $t['payment_mode'] }}</th>
            <th>{{ $t['sales_return'] }}</th>
            <th>{{ $t['tax'] }}</th>
            <th>{{ $t['payment'] }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['owner'] }}</td>
                <td>{{ $row['payment_mode'] }}</td>
                <td>{{ number_format($row['sales_return'], 2) }}</td>
                <td>{{ number_format($row['tax'], 2) }}</td>
                <td>{{ number_format($row['payment'], 2) }}</td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="3">{{ $t['total'] }}</td>
            <td>{{ number_format($totals['sales_return'], 2) }}</td>
            <td>{{ number_format($totals['tax'], 2) }}</td>
            <td>{{ number_format($totals['payment'], 2) }}</td>
        </tr>
        </tbody>
    </table>
@endif

</body>
</html>