@php
    $isArabic = app()->getLocale() === 'ar';

    $supplierName = $isArabic
        ? ($invoice->supplier?->supplier_name_ar ?? $invoice->supplier?->supplier_name_en)
        : ($invoice->supplier?->supplier_name_en ?? $invoice->supplier?->supplier_name_ar);

    $supplierAddress = trim(
        ($invoice->supplier?->address_line_1 ?? '') . ' ' .
        ($invoice->supplier?->city ?? '') . ' ' .
        ($invoice->supplier?->country ?? '')
    );

    $t = [
        'title' => $isArabic ? 'فاتورة مشتريات' : 'Purchase Invoice',
        'invoice_no' => $isArabic ? 'رقم الفاتورة' : 'Invoice Number',
        'supplier' => $isArabic ? 'المورد' : 'Supplier',
        'address' => $isArabic ? 'العنوان' : 'Address',
        'supplier_invoice_no' => $isArabic ? 'رقم فاتورة المورد' : 'Supplier Invoice No',
        'posting_date' => $isArabic ? 'تاريخ الترحيل' : 'Posting Date',
        'due_date' => $isArabic ? 'تاريخ الاستحقاق' : 'Due By',
        'no' => $isArabic ? 'الرقم' : 'No',
        'item' => $isArabic ? 'الصنف' : 'Item Name',
        'qty' => $isArabic ? 'الكمية' : 'Quantity',
        'rate' => $isArabic ? 'السعر' : 'Rate',
        'amount' => $isArabic ? 'المبلغ' : 'Amount',
        'sub_total' => $isArabic ? 'الإجمالي الفرعي' : 'Sub Total',
        'vat' => $isArabic ? 'الضريبة' : 'VAT',
        'fees' => $isArabic ? 'الرسوم' : 'Fees',
        'discount' => $isArabic ? 'الخصم' : 'Discount',
        'grand_total' => $isArabic ? 'الإجمالي النهائي' : 'Grand Total',
        'amount_words' => $isArabic ? 'المبلغ كتابة' : 'Amount in Words',
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
            margin-bottom: 10px;
        }

        .header-table,
        .info-table,
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .header-table td,
        .info-table td {
            padding: 5px;
            border: 1px solid #ccc;
        }

        .items-table th,
        .items-table td,
        .totals-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .items-table th {
            background: #eeeeee;
            font-weight: bold;
        }

        .label {
            font-weight: bold;
            background: #f5f5f5;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }
    </style>
</head>
<body>

<h2>{{ $t['title'] }}</h2>

<table class="header-table">
    <tr>
        <td class="label">{{ $t['invoice_no'] }}</td>
        <td>{{ $invoice->invoice_number }}</td>
        <td class="label">{{ $t['posting_date'] }}</td>
        <td>{{ $invoice->posting_date }}</td>
    </tr>
    <tr>
        <td class="label">{{ $t['supplier_invoice_no'] }}</td>
        <td>{{ $invoice->supplier_invoice_no ?? '-' }}</td>
        <td class="label">{{ $t['due_date'] }}</td>
        <td>{{ $invoice->due_date ?? '-' }}</td>
    </tr>
</table>

<table class="info-table">
    <tr>
        <td class="label">{{ $t['supplier'] }}</td>
        <td>{{ $supplierName }}</td>
    </tr>
    <tr>
        <td class="label">{{ $t['address'] }}</td>
        <td>{{ $supplierAddress ?: '-' }}</td>
    </tr>
</table>

<table class="items-table">
    <thead>
    <tr>
        <th>{{ $t['no'] }}</th>
        <th>{{ $t['item'] }}</th>
        <th>{{ $t['qty'] }}</th>
        <th>{{ $t['rate'] }}</th>
        <th>{{ $t['amount'] }}</th>
    </tr>
    </thead>

    <tbody>
    @foreach($invoice->items as $index => $item)
        @php
            $itemName = $isArabic
                ? ($item->item_name_ar ?? $item->item_name_en)
                : ($item->item_name_en ?? $item->item_name_ar);
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $itemName }}</td>
            <td>{{ number_format((float) $item->quantity, 2) }}</td>
            <td>{{ number_format((float) $item->rate, 2) }}</td>
            <td>{{ number_format((float) $item->amount, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="totals-table">
    <tr>
        <td class="label">{{ $t['sub_total'] }}</td>
        <td>{{ number_format((float) $invoice->net_total, 2) }}</td>
    </tr>
    <tr>
        <td class="label">{{ $t['vat'] }}</td>
        <td>{{ number_format((float) $invoice->tax_total, 2) }}</td>
    </tr>
    <tr>
        <td class="label">{{ $t['fees'] }}</td>
        <td>{{ number_format((float) $invoice->fees_total, 2) }}</td>
    </tr>
    <tr>
        <td class="label">{{ $t['discount'] }}</td>
        <td>{{ number_format((float) $invoice->discount_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="label">{{ $t['grand_total'] }}</td>
        <td><strong>{{ number_format((float) $invoice->grand_total, 2) }}</strong></td>
    </tr>
</table>

<p>
    <strong>{{ $t['amount_words'] }}:</strong>
    {{ number_format((float) $invoice->grand_total, 2) }}
</p>

</body>
</html>