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
            font-size: 12px;
            direction: {{ $isArabic ? 'rtl' : 'ltr' }};
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        .period {
            text-align: center;
            margin-bottom: 15px;
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
    </style>
</head>
<body>

<h2>
    {{ $isArabic
        ? 'تقرير كشف حساب العملاء'
        : 'Customer Ledger Report'
    }}
</h2>

<div class="period">
    {{ $isArabic ? 'من' : 'From' }}
    : {{ $fromDate }}

    &nbsp;&nbsp;&nbsp;

    {{ $isArabic ? 'إلى' : 'To' }}
    : {{ $toDate }}
</div>

<table>
    <thead>
        <tr>
            <th>
                {{ $isArabic ? 'العميل' : 'Customer' }}
            </th>

            <th>
                {{ $isArabic ? 'الرصيد الافتتاحي' : 'Opening Balance' }}
            </th>

            <th>
                {{ $isArabic ? 'قيمة الفواتير' : 'Invoice Amount' }}
            </th>

            <th>
                {{ $isArabic ? 'المبلغ المدفوع' : 'Paid Amount' }}
            </th>

            <th>
                {{ $isArabic ? 'الإشعارات الدائنة' : 'Credit Notes' }}
            </th>

            <th>
                {{ $isArabic ? 'الرصيد الختامي' : 'Closing Balance' }}
            </th>

            <th>
                {{ $isArabic ? 'مدين / دائن' : 'Dr / Cr' }}
            </th>
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['customer'] }}</td>

                <td>{{ number_format($row['opening_balance'], 2) }}</td>

                <td>{{ number_format($row['invoice_amount'], 2) }}</td>

                <td>{{ number_format($row['paid_amount'], 2) }}</td>

                <td>{{ number_format($row['credit_note'], 2) }}</td>

                <td>{{ number_format($row['closing_balance'], 2) }}</td>

                <td>{{ $row['dr_cr'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>