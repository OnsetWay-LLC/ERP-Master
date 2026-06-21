@php
    $isArabic = app()->getLocale() === 'ar';

    $t = [
        'title' => $isArabic ? 'تقرير سجل المشتريات' : 'Purchase Register Report',
        'from' => $isArabic ? 'من' : 'From',
        'to' => $isArabic ? 'إلى' : 'To',
        'voucher_type' => $isArabic ? 'نوع المستند' : 'Voucher Type',
        'voucher' => $isArabic ? 'المستند' : 'Voucher',
        'posting_date' => $isArabic ? 'تاريخ العملية' : 'Posting Date',
        'supplier_name' => $isArabic ? 'اسم المورد' : 'Supplier Name',
        'payable_account' => $isArabic ? 'حساب الذمم الدائنة' : 'Payable Account',
        'purchase_order' => $isArabic ? 'أمر الشراء' : 'Purchase Order',
        'stock_account' => $isArabic ? 'قيمة المخزون' : 'Stock Account',
        'vat' => $isArabic ? 'الضريبة' : 'VAT',
        'fees' => $isArabic ? 'الرسوم' : 'Fees',
        'discount' => $isArabic ? 'الخصم' : 'Discount',
        'net_total' => $isArabic ? 'الصافي' : 'Net Total',
        'grand_total' => $isArabic ? 'الإجمالي' : 'Grand Total',
        'outstanding' => $isArabic ? 'المتبقي' : 'Outstanding',
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
            font-size: 8.5px;
            direction: {{ $isArabic ? 'rtl' : 'ltr' }};
        }

        h2 {
            text-align: center;
            margin-bottom: 8px;
        }

        .period {
            text-align: center;
            margin-bottom: 10px;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
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
        .debit-note-row {
    background: #fff4f4;
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
        <th>{{ $t['voucher_type'] }}</th>
        <th>{{ $t['voucher'] }}</th>
        <th>{{ $t['posting_date'] }}</th>
        <th>{{ $t['supplier_name'] }}</th>
        <th>{{ $t['payable_account'] }}</th>
        <th>{{ $t['purchase_order'] }}</th>
        <th>{{ $t['stock_account'] }}</th>
        <th>{{ $t['vat'] }}</th>
        <th>{{ $t['fees'] }}</th>
        <th>{{ $t['discount'] }}</th>
        <th>{{ $t['net_total'] }}</th>
        <th>{{ $t['grand_total'] }}</th>
        <th>{{ $t['outstanding'] }}</th>
    </tr>
    </thead>

    <tbody>
   @foreach($rows as $row)
    <tr class="{{ $row['voucher_type'] === 'Debit Note' ? 'debit-note-row' : '' }}">
        <tr>
            <td>{{ $row['voucher_type'] }}</td>
            <td>{{ $row['voucher'] }}</td>
            <td>{{ $row['posting_date'] }}</td>
            <td>{{ $row['supplier_name'] }}</td>
            <td>{{ $row['payable_account'] ?? '-' }}</td>
            <td>{{ $row['purchase_order'] }}</td>
            <td>{{ number_format($row['stock_account'], 2) }}</td>
            <td>{{ number_format($row['vat'], 2) }}</td>
            <td>{{ number_format($row['fees'], 2) }}</td>
            <td>{{ number_format($row['discount'], 2) }}</td>
            <td>{{ number_format($row['net_total'], 2) }}</td>
            <td>{{ number_format($row['grand_total'], 2) }}</td>
            <td>{{ number_format($row['outstanding'], 2) }}</td>
        </tr>
    @endforeach

    <tr class="total-row">
        <td colspan="6">{{ $t['total'] }}</td>
        <td>{{ number_format($totals['stock_account'], 2) }}</td>
        <td>{{ number_format($totals['vat'], 2) }}</td>
        <td>{{ number_format($totals['fees'], 2) }}</td>
        <td>{{ number_format($totals['discount'], 2) }}</td>
        <td>{{ number_format($totals['net_total'], 2) }}</td>
        <td>{{ number_format($totals['grand_total'], 2) }}</td>
        <td>{{ number_format($totals['outstanding'], 2) }}</td>
    </tr>
    </tbody>
</table>
@endif

</body>
</html>