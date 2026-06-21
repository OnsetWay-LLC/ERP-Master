@php
    $isArabic = app()->getLocale() === 'ar';

    $t = [
        'title' => $isArabic ? 'تقرير سجل المبيعات حسب الصنف' : 'Item Wise Sales Register Report',
        'from' => $isArabic ? 'من' : 'From',
        'to' => $isArabic ? 'إلى' : 'To',
        'item_code' => $isArabic ? 'كود الصنف' : 'Item Code',
        'item_name' => $isArabic ? 'اسم الصنف' : 'Item Name',
        'item_group' => $isArabic ? 'مجموعة الصنف' : 'Item Group',
        'invoice' => $isArabic ? 'الفاتورة' : 'Invoice',
        'posting_date' => $isArabic ? 'تاريخ الفاتورة' : 'Posting Date',
        'customer_name' => $isArabic ? 'اسم العميل' : 'Customer Name',
        'receivable_account' => $isArabic ? 'حساب الذمم' : 'Receivable Account',
        'mode_of_payment' => $isArabic ? 'طريقة الدفع' : 'Mode of Payment',
        'sales_order_no' => $isArabic ? 'طلب البيع' : 'Sales Order No',
        'income_account' => $isArabic ? 'حساب الإيراد' : 'Income Account',
        'stock_qty' => $isArabic ? 'الكمية' : 'Stock Qty',
        'rate' => $isArabic ? 'السعر' : 'Rate',
        'amount' => $isArabic ? 'المبلغ' : 'Amount',
        'tax_rate' => $isArabic ? 'نسبة الضريبة' : 'Tax Rate %',
        'vat_amount' => $isArabic ? 'قيمة الضريبة' : 'VAT Amount',
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
            font-size: 7.8px;
            direction: {{ $isArabic ? 'rtl' : 'ltr' }};
        }

        h2 {
            text-align: center;
            margin-bottom: 6px;
        }

        .period {
            text-align: center;
            margin-bottom: 8px;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            white-space: nowrap;
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
            font-size: 12px;
            margin-top: 30px;
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
            <th>{{ $t['item_code'] }}</th>
            <th>{{ $t['item_name'] }}</th>
            <th>{{ $t['item_group'] }}</th>
            <th>{{ $t['invoice'] }}</th>
            <th>{{ $t['posting_date'] }}</th>
            <th>{{ $t['customer_name'] }}</th>
            <th>{{ $t['receivable_account'] }}</th>
            <th>{{ $t['mode_of_payment'] }}</th>
            <th>{{ $t['sales_order_no'] }}</th>
            <th>{{ $t['income_account'] }}</th>
            <th>{{ $t['stock_qty'] }}</th>
            <th>{{ $t['rate'] }}</th>
            <th>{{ $t['amount'] }}</th>
            <th>{{ $t['tax_rate'] }}</th>
            <th>{{ $t['vat_amount'] }}</th>
            <th>{{ $t['total'] }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['item_code'] }}</td>
                <td>{{ $row['item_name'] }}</td>
                <td>{{ $row['item_group'] ?? '-' }}</td>
                <td>{{ $row['invoice'] }}</td>
                <td>{{ $row['posting_date'] }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td>{{ $row['receivable_account'] }}</td>
                <td>{{ $row['mode_of_payment'] }}</td>
                <td>{{ $row['sales_order_no'] }}</td>
                <td>{{ $row['income_account'] }}</td>
                <td>{{ number_format($row['stock_qty'], 2) }}</td>
                <td>{{ number_format($row['rate'], 2) }}</td>
                <td>{{ number_format($row['amount'], 2) }}</td>
                <td>{{ number_format($row['tax_rate'], 2) }}</td>
                <td>{{ number_format($row['vat_amount'], 2) }}</td>
                <td>{{ number_format($row['total'], 2) }}</td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="10">{{ $isArabic ? 'الإجمالي' : 'Total' }}</td>
            <td>{{ number_format($totals['stock_qty'], 2) }}</td>
            <td>{{ number_format($totals['rate'], 2) }}</td>
            <td>{{ number_format($totals['amount'], 2) }}</td>
            <td>{{ number_format($totals['tax_rate'], 2) }}</td>
            <td>{{ number_format($totals['vat_amount'], 2) }}</td>
            <td>{{ number_format($totals['total'], 2) }}</td>
        </tr>
        </tbody>
    </table>
@endif

</body>
</html>