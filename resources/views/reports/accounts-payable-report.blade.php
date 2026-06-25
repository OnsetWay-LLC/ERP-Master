@php
    $isArabic = app()->getLocale() === 'ar';

    $t = [
        'title' => $isArabic ? 'تقرير الذمم الدائنة' : 'Accounts Payable Report',
        'from' => $isArabic ? 'من' : 'From',
        'to' => $isArabic ? 'إلى' : 'To',
        'posting_date' => $isArabic ? 'تاريخ العملية' : 'Posting Date',
        'party_type' => $isArabic ? 'نوع الطرف' : 'Party Type',
        'party' => $isArabic ? 'الطرف' : 'Party',
        'payable_account' => $isArabic ? 'حساب الذمم الدائنة' : 'Payable Account',
        'voucher_type' => $isArabic ? 'نوع المستند' : 'Voucher Type',
        'voucher_no' => $isArabic ? 'رقم المستند' : 'Voucher No',
        'due_date' => $isArabic ? 'تاريخ الاستحقاق' : 'Due Date',
        'invoice_amount' => $isArabic ? 'قيمة الفاتورة' : 'Invoice Amount',
        'paid_amount' => $isArabic ? 'المدفوع' : 'Paid Amount',
        'debit_note' => $isArabic ? 'إشعار مدين' : 'Debit Note',
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
            font-size: 8px;
            direction: {{ $isArabic ? 'rtl' : 'ltr' }};
        }

        h2 {
            text-align: center;
            margin-bottom: 8px;
        }

        .period {
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

<div class="period">
    {{ $t['from'] }}: {{ $fromDate }}
    &nbsp;&nbsp;&nbsp;
    {{ $t['to'] }}: {{ $toDate }}
</div>

<table>
    <thead>
        <tr>
            <th>{{ $t['posting_date'] }}</th>
            <th>{{ $t['party_type'] }}</th>
            <th>{{ $t['party'] }}</th>
            <th>{{ $t['payable_account'] }}</th>
            <th>{{ $t['voucher_type'] }}</th>
            <th>{{ $t['voucher_no'] }}</th>
            <th>{{ $t['due_date'] }}</th>
            <th>{{ $t['invoice_amount'] }}</th>
            <th>{{ $t['paid_amount'] }}</th>
            <th>{{ $t['debit_note'] }}</th>
            <th>{{ $t['outstanding'] }}</th>
        </tr>
    </thead>

    <tbody>
    @forelse($rows as $row)
        <tr>
            <td>{{ $row['posting_date'] }}</td>
            <td>{{ $row['party_type'] }}</td>
            <td>{{ $row['party'] }}</td>
            <td>{{ $row['payable_account'] }}</td>
            <td>{{ $row['voucher_type'] }}</td>
            <td>{{ $row['voucher_no'] }}</td>
            <td>{{ $row['due_date'] ?? '-' }}</td>
            <td>{{ number_format($row['invoice_amount'], 2) }}</td>
            <td>{{ number_format($row['paid_amount'], 2) }}</td>
            <td>{{ number_format($row['debit_note'], 2) }}</td>
            <td>{{ number_format($row['outstanding'], 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="11" class="no-data">{{ $t['no_data'] }}</td>
        </tr>
    @endforelse
    </tbody>

    <tfoot>
        <tr class="total-row">
            <td colspan="7">{{ $t['total'] }}</td>
            <td>{{ number_format($totals['invoice_amount'], 2) }}</td>
            <td>{{ number_format($totals['paid_amount'], 2) }}</td>
            <td>{{ number_format($totals['debit_note'], 2) }}</td>
            <td>{{ number_format($totals['outstanding'], 2) }}</td>
        </tr>
    </tfoot>
</table>

</body>
</html>