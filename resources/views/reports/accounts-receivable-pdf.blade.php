@php
    $isArabic = app()->getLocale() === 'ar';
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
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background: #eeeeee;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background: #f3f3f3;
        }
    </style>
</head>
<body>

<h2>
    {{ $isArabic ? 'تقرير الذمم المدينة' : 'Accounts Receivable Report' }}
</h2>

<table>
    <thead>
    <tr>
        <th>{{ $isArabic ? 'تاريخ العملية' : 'Posting Date' }}</th>
        <th>{{ $isArabic ? 'نوع الطرف' : 'Party Type' }}</th>
        <th>{{ $isArabic ? 'الطرف' : 'Party' }}</th>
        <th>{{ $isArabic ? 'حساب الذمم المدينة' : 'Receivable Account' }}</th>
        <th>{{ $isArabic ? 'نوع المستند' : 'Voucher Type' }}</th>
        <th>{{ $isArabic ? 'رقم المستند' : 'Voucher No' }}</th>
        <th>{{ $isArabic ? 'تاريخ الاستحقاق' : 'Due Date' }}</th>
        <th>{{ $isArabic ? 'قيمة الفاتورة' : 'Invoice Amount' }}</th>
        <th>{{ $isArabic ? 'المبلغ المدفوع' : 'Paid Amount' }}</th>
        <th>{{ $isArabic ? 'الإشعار الدائن' : 'Credit Note' }}</th>
        <th>{{ $isArabic ? 'المتبقي' : 'Outstanding' }}</th>
    </tr>
    </thead>

    <tbody>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['posting_date'] }}</td>
            <td>{{ $isArabic ? 'عميل' : $row['party_type'] }}</td>
            <td>{{ $row['party'] }}</td>
            <td>{{ $row['receivable_account'] }}</td>
            <td>{{ $row['voucher_type'] }}</td>
            <td>{{ $row['voucher_no'] }}</td>
            <td>{{ $row['due_date'] }}</td>
            <td>{{ number_format($row['invoice_amount'], 2) }}</td>
            <td>{{ number_format($row['paid_amount'], 2) }}</td>
            <td>{{ number_format($row['credit_note'], 2) }}</td>
            <td>{{ number_format($row['outstanding'], 2) }}</td>
        </tr>
    @endforeach

    <tr class="total-row">
        <td colspan="7">{{ $isArabic ? 'الإجمالي' : 'Total' }}</td>
        <td>{{ number_format($totals['invoice_amount'], 2) }}</td>
        <td>{{ number_format($totals['paid_amount'], 2) }}</td>
        <td>{{ number_format($totals['credit_note'], 2) }}</td>
        <td>{{ number_format($totals['outstanding'], 2) }}</td>
    </tr>
    </tbody>
</table>

</body>
</html>