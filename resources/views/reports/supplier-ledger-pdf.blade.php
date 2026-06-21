@php
    $isArabic = app()->getLocale() === 'ar';

    $t = [
        'title' => $isArabic ? 'تقرير دفتر الموردين' : 'Supplier Ledger Report',
        'from' => $isArabic ? 'من' : 'From',
        'to' => $isArabic ? 'إلى' : 'To',
        'supplier' => $isArabic ? 'المورد' : 'Supplier',
        'opening_balance' => $isArabic ? 'الرصيد الافتتاحي' : 'Opening Balance',
        'invoice_amount' => $isArabic ? 'قيمة الفواتير' : 'Invoice Amount',
        'paid_amount' => $isArabic ? 'المبلغ المدفوع' : 'Paid Amount',
        'debit_note' => $isArabic ? 'إشعار مدين' : 'Debit Note',
        'closing_balance' => $isArabic ? 'الرصيد الختامي' : 'Closing Balance',
        'dr_cr' => $isArabic ? 'مدين/دائن' : 'Dr/Cr',
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
            font-size: 10px;
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
            padding: 5px;
            text-align: center;
            white-space: nowrap;
        }

        th {
            background: #eeeeee;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background: #f3f3f3;
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
            <th>{{ $t['supplier'] }}</th>
            <th>{{ $t['opening_balance'] }}</th>
            <th>{{ $t['invoice_amount'] }}</th>
            <th>{{ $t['paid_amount'] }}</th>
            <th>{{ $t['debit_note'] }}</th>
            <th>{{ $t['closing_balance'] }}</th>
            <th>{{ $t['dr_cr'] }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['supplier'] }}</td>
                <td>{{ number_format($row['opening_balance'], 2) }}</td>
                <td>{{ number_format($row['invoice_amount'], 2) }}</td>
                <td>{{ number_format($row['paid_amount'], 2) }}</td>
                <td>{{ number_format($row['debit_note'], 2) }}</td>
                <td>{{ number_format($row['closing_balance'], 2) }}</td>
                <td>{{ $row['dr_cr'] }}</td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td>{{ $t['total'] }}</td>
            <td>{{ number_format($totals['opening_balance'], 2) }}</td>
            <td>{{ number_format($totals['invoice_amount'], 2) }}</td>
            <td>{{ number_format($totals['paid_amount'], 2) }}</td>
            <td>{{ number_format($totals['debit_note'], 2) }}</td>
            <td>{{ number_format(abs($totals['closing_balance']), 2) }}</td>
            <td>
                @if($totals['closing_balance'] > 0)
                    Cr
                @elseif($totals['closing_balance'] < 0)
                    Dr
                @else
                    -
                @endif
            </td>
        </tr>
        </tbody>
    </table>
@endif

</body>
</html>