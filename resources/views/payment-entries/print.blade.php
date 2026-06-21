@php
    $isArabic = app()->getLocale() === 'ar';

    $t = [
        'title' => $isArabic ? 'سند دفع' : 'Payment Entry',
        'series' => $isArabic ? 'الرقم' : 'Series',
        'posting_date' => $isArabic ? 'تاريخ الدفع' : 'Posting Date',
        'supplier' => $isArabic ? 'المورد' : 'Supplier',
        'payment_mode' => $isArabic ? 'طريقة الدفع' : 'Payment Mode',
        'paid_amount' => $isArabic ? 'المبلغ المدفوع' : 'Paid Amount',
        'paid_from' => $isArabic ? 'حساب الدفع' : 'Paid From Account',
        'payable' => $isArabic ? 'حساب الذمم' : 'Payable Account',
        'reference_no' => $isArabic ? 'رقم المرجع' : 'Reference No',
        'reference_date' => $isArabic ? 'تاريخ المرجع' : 'Reference Date',
        'remarks' => $isArabic ? 'ملاحظات' : 'Remarks',
        'invoice' => $isArabic ? 'فاتورة المشتريات' : 'Purchase Invoice',
        'invoice_amount' => $isArabic ? 'قيمة الفاتورة' : 'Invoice Amount',
        'outstanding_before' => $isArabic ? 'المتبقي قبل الدفع' : 'Outstanding Before',
        'allocated_amount' => $isArabic ? 'المبلغ المخصص' : 'Allocated Amount',
        'outstanding_after' => $isArabic ? 'المتبقي بعد الدفع' : 'Outstanding After',
    ];

    $supplierName = $isArabic
        ? ($paymentEntry->supplier?->supplier_name_ar ?? $paymentEntry->supplier?->supplier_name_en)
        : ($paymentEntry->supplier?->supplier_name_en ?? $paymentEntry->supplier?->supplier_name_ar);

    $paidFromName = $isArabic
        ? ($paymentEntry->paidFromAccount?->name_ar ?? $paymentEntry->paidFromAccount?->name_en)
        : ($paymentEntry->paidFromAccount?->name_en ?? $paymentEntry->paidFromAccount?->name_ar);

    $payableName = $isArabic
        ? ($paymentEntry->payableAccount?->name_ar ?? $paymentEntry->payableAccount?->name_en)
        : ($paymentEntry->payableAccount?->name_en ?? $paymentEntry->payableAccount?->name_ar);
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
            margin-bottom: 18px;
        }

        .info-table,
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .info-table td,
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .label {
            font-weight: bold;
            background: #f1f1f1;
            width: 22%;
        }

        .items-table th {
            background: #eaeaea;
            text-align: center;
        }

        .items-table td {
            text-align: center;
        }
    </style>
</head>

<body>

<h2>{{ $t['title'] }}</h2>

<table class="info-table">
    <tr>
        <td class="label">{{ $t['series'] }}</td>
        <td>{{ $paymentEntry->series }}</td>
        <td class="label">{{ $t['posting_date'] }}</td>
        <td>{{ $paymentEntry->posting_date?->format('Y-m-d') }}</td>
    </tr>

    <tr>
        <td class="label">{{ $t['supplier'] }}</td>
        <td>{{ $supplierName }}</td>
        <td class="label">{{ $t['payment_mode'] }}</td>
        <td>{{ strtoupper($paymentEntry->payment_mode) }}</td>
    </tr>

    <tr>
        <td class="label">{{ $t['paid_from'] }}</td>
        <td>{{ $paidFromName }}</td>
        <td class="label">{{ $t['payable'] }}</td>
        <td>{{ $payableName }}</td>
    </tr>

    <tr>
        <td class="label">{{ $t['paid_amount'] }}</td>
        <td>{{ number_format($paymentEntry->paid_amount, 2) }}</td>
        <td class="label">{{ $t['reference_no'] }}</td>
        <td>{{ $paymentEntry->reference_no ?? '-' }}</td>
    </tr>

    <tr>
        <td class="label">{{ $t['reference_date'] }}</td>
        <td>{{ $paymentEntry->reference_date?->format('Y-m-d') ?? '-' }}</td>
        <td class="label">{{ $t['remarks'] }}</td>
        <td>{{ $paymentEntry->remarks ?? '-' }}</td>
    </tr>
</table>

<table class="items-table">
    <thead>
    <tr>
        <th>{{ $t['invoice'] }}</th>
        <th>{{ $t['invoice_amount'] }}</th>
        <th>{{ $t['outstanding_before'] }}</th>
        <th>{{ $t['allocated_amount'] }}</th>
        <th>{{ $t['outstanding_after'] }}</th>
    </tr>
    </thead>

    <tbody>
    @foreach($paymentEntry->references as $reference)
        <tr>
            <td>{{ $reference->purchaseInvoice?->invoice_number }}</td>
            <td>{{ number_format($reference->invoice_amount, 2) }}</td>
            <td>{{ number_format($reference->outstanding_before_payment, 2) }}</td>
            <td>{{ number_format($reference->allocated_amount, 2) }}</td>
            <td>{{ number_format($reference->outstanding_after_payment, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>