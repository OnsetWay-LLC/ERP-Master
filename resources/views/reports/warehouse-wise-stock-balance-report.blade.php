@php
    $isArabic = app()->getLocale() === 'ar';

    $t = [
        'title' => $isArabic ? 'تقرير رصيد المخزون حسب المستودع' : 'Warehouse Wise Stock Balance Report',
        'warehouse' => $isArabic ? 'المستودع' : 'Warehouse',
        'warehouse_type' => $isArabic ? 'نوع المستودع' : 'Warehouse Type',
        'sales_person' => $isArabic ? 'مندوب المبيعات' : 'Sales Person',
        'item_name' => $isArabic ? 'اسم المادة' : 'Item Name',
        'in_qty' => $isArabic ? 'الكمية الداخلة' : 'In Qty',
        'out_qty' => $isArabic ? 'الكمية الخارجة' : 'Out Qty',
        'balance_qty' => $isArabic ? 'الرصيد النهائي' : 'Balance Qty',
        'valuation_rate' => $isArabic ? 'سعر التقييم' : 'Valuation Rate',
        'stock_value' => $isArabic ? 'قيمة المخزون' : 'Stock Value',
        'total' => $isArabic ? 'الإجمالي' : 'Total',
        'no_data' => $isArabic ? 'لا توجد أرصدة مخزون للمستودعات' : 'No Warehouse Stock Balance Records Found',
    ];
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: dejavusans;
            font-size: 8px;
            direction: {{ $isArabic ? 'rtl' : 'ltr' }};
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
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
            padding: 20px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<h2>{{ $t['title'] }}</h2>

<table>
    <thead>
        <tr>
            <th>{{ $t['warehouse'] }}</th>
            <th>{{ $t['warehouse_type'] }}</th>
            <th>{{ $t['sales_person'] }}</th>
            <th>{{ $t['item_name'] }}</th>
            <th>{{ $t['in_qty'] }}</th>
            <th>{{ $t['out_qty'] }}</th>
            <th>{{ $t['balance_qty'] }}</th>
            <th>{{ $t['valuation_rate'] }}</th>
            <th>{{ $t['stock_value'] }}</th>
        </tr>
    </thead>

    <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row['warehouse'] }}</td>
                <td>{{ $row['warehouse_type'] }}</td>
                <td>{{ $row['sales_person'] }}</td>
                <td>{{ $row['item_name'] }}</td>
                <td>{{ number_format($row['in_qty'], 2) }}</td>
                <td>{{ number_format($row['out_qty'], 2) }}</td>
                <td>{{ number_format($row['balance_qty'], 2) }}</td>
                <td>{{ number_format($row['valuation_rate'], 2) }}</td>
                <td>{{ number_format($row['stock_value'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="no-data">
                    {{ $t['no_data'] }}
                </td>
            </tr>
        @endforelse
    </tbody>

    <tfoot>
        <tr class="total-row">
            <td colspan="4">{{ $t['total'] }}</td>
            <td>{{ number_format($totals['in_qty'], 2) }}</td>
            <td>{{ number_format($totals['out_qty'], 2) }}</td>
            <td>{{ number_format($totals['balance_qty'], 2) }}</td>
            <td>-</td>
            <td>{{ number_format($totals['stock_value'], 2) }}</td>
        </tr>
    </tfoot>
</table>

</body>
</html>