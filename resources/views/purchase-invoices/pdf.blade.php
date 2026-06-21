@php
    $isArabic = app()->getLocale() === 'ar';

    $companyName = $isArabic
        ? ($invoice->company?->name_ar ?? $invoice->company?->name_en)
        : ($invoice->company?->name_en ?? $invoice->company?->name_ar);

    $supplierName = $isArabic
        ? ($invoice->supplier?->supplier_name_ar ?? $invoice->supplier?->supplier_name_en)
        : ($invoice->supplier?->supplier_name_en ?? $invoice->supplier?->supplier_name_ar);

    $supplierAddress = trim(
        ($invoice->supplier?->address_line_1 ?? '') . ' ' .
        ($invoice->supplier?->city ?? '') . ' ' .
        ($invoice->supplier?->country ?? '')
    );

    $currency = $invoice->company?->currency_code ?? 'JOD';

    $t = [
        'title' => $isArabic ? 'فاتورة مشتريات' : 'Purchase Invoice',
        'supplier_details' => $isArabic ? 'بيانات المورد' : 'Supplier Details',
        'supplier_invoice_no' => $isArabic ? 'رقم فاتورة المورد' : 'Supplier Invoice No',
        'posting_date' => $isArabic ? 'تاريخ الترحيل' : 'Posting Date',
        'due_date' => $isArabic ? 'تاريخ الاستحقاق' : 'Due Date',
        'item' => $isArabic ? 'الصنف' : 'Item',
        'qty' => $isArabic ? 'الكمية' : 'Qty',
        'rate' => $isArabic ? 'السعر' : 'Rate',
        'amount' => $isArabic ? 'المبلغ' : 'Amount',
        'sub_total' => $isArabic ? 'الإجمالي الفرعي' : 'Sub Total',
        'vat' => $isArabic ? 'الضريبة' : 'VAT',
        'fees' => $isArabic ? 'الرسوم' : 'Fees',
        'discount' => $isArabic ? 'الخصم' : 'Discount',
        'grand_total' => $isArabic ? 'الإجمالي النهائي' : 'Grand Total',
        'amount_words' => $isArabic ? 'المبلغ كتابة' : 'Amount in Words',
        'printed_at' => $isArabic ? 'تاريخ الطباعة' : 'Printed at',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ $isArabic ? 'ar' : 'en' }}">
<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: dejavusans;
            font-size: 11px;
            direction: {{ $isArabic ? 'rtl' : 'ltr' }};
            color: #111827;
        }

        .page {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 18px;
        }

        .top {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .top td {
            vertical-align: top;
        }

        .invoice-badge {
            display: inline-block;
            background: #001f3f;
            color: #ffffff;
            padding: 5px 8px;
            font-size: 15px;
            font-weight: bold;
        }

        .company-box {
            text-align: {{ $isArabic ? 'left' : 'right' }};
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
        }

        .invoice-title {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }

        .info-table td {
            vertical-align: top;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            line-height: 1.8;
        }

        .details-table td {
            padding: 4px 0;
            vertical-align: middle;
        }

        .label {
            color: #6b7280;
            font-size: 11px;
        }

        .value {
            font-weight: bold;
            font-size: 13px;
            color: #111827;
        }

        .details-table .label {
            width: 48%;
        }

        .details-table .value {
            width: 52%;
            text-align: {{ $isArabic ? 'left' : 'right' }};
        }

        .supplier-card {
            background: #f1f5ff;
            padding: 10px;
            border-radius: 6px;
            width: 85%;
        }

        .supplier-name {
            font-size: 15px;
            font-weight: bold;
        }

        .supplier-address {
            color: #6b7280;
            font-size: 11px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }

        .items-table th {
            background: #001f3f;
            color: #ffffff;
            padding: 9px;
            font-size: 11px;
            text-align: center;
        }

        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 9px;
            font-size: 11px;
            text-align: center;
        }
                .item-name {
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .totals-table .total-row td {
            font-weight: bold;
            font-size: 13px;
            color: #001f3f;
        }

        .amount-words-row td {
            border-bottom: none;
            padding-top: 12px;
            line-height: 1.8;
        }

        .footer {
            margin-top: 45px;
            padding-top: 12px;
            border-top: 1px dashed #d1d5db;
            text-align: {{ $isArabic ? 'left' : 'right' }};
            color: #374151;
            font-size: 10px;
        }
    </style>
</head>

<body>
<div class="page">

    <table class="top">
        <tr>
            <td style="width: 40%;">
                <span class="invoice-badge">{{ $invoice->invoice_number }}</span>
            </td>

            <td style="width: 60%;" class="company-box">
                <div class="company-name">{{ $companyName ?? '-' }}</div>
                <div class="invoice-title">{{ $t['title'] }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 45%;">
                <table class="details-table">
                    <tr>
                        <td class="label">{{ $t['supplier_invoice_no'] }}</td>
                        <td class="value">{{ $invoice->supplier_invoice_no ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ $t['posting_date'] }}</td>
                        <td class="value">{{ $invoice->posting_date ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="label">{{ $t['due_date'] }}</td>
                        <td class="value">{{ $invoice->due_date ?? '-' }}</td>
                    </tr>
                </table>
            </td>

            <td style="width: 10%;"></td>

            <td style="width: 45%;">
                <table class="details-table">
    <tr>
        <td class="label">
            {{ $isArabic ? 'المورد' : 'Supplier' }}
        </td>
        <td class="value">
            {{ $supplierName ?? '-' }}
        </td>
    </tr>

    <tr>
        <td class="label">
            {{ $isArabic ? 'العنوان' : 'Address' }}
        </td>
        <td class="value">
            {{ $supplierAddress ?: '-' }}
        </td>
    </tr>
</table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 35%;">{{ $t['item'] }}</th>
                <th style="width: 15%;">{{ $t['qty'] }}</th>
                <th style="width: 20%;">{{ $t['rate'] }}</th>
                <th style="width: 20%;">{{ $t['amount'] }}</th>
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
                    <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="item-name">{{ $itemName }}</td>
                    <td>{{ number_format((float) $item->quantity, 2) }}</td>
                    <td>{{ number_format((float) $item->rate, 2) }}</td>
                    <td>{{ number_format((float) $item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td style="width: 46%;">
                <table class="totals-table">
                    <tr>
                        <td>{{ $t['sub_total'] }}</td>
                        <td>{{ number_format((float) $invoice->net_total, 2) }}</td>
                    </tr>

                    <tr>
                        <td>{{ $t['vat'] }}</td>
                        <td>{{ number_format((float) $invoice->tax_total, 2) }}</td>
                    </tr>

                    <tr>
                        <td>{{ $t['fees'] }}</td>
                        <td>{{ number_format((float) $invoice->fees_total, 2) }}</td>
                    </tr>

                    <tr>
                        <td>{{ $t['discount'] }}</td>
                        <td>- {{ number_format((float) $invoice->discount_amount, 2) }}</td>
                    </tr>

                    <tr class="total-row">
                        <td>{{ $t['grand_total'] }}</td>
                        <td>{{ $currency }} {{ number_format((float) $invoice->grand_total, 2) }}</td>
                    </tr>

                    <tr class="amount-words-row">
                        <td colspan="2">
                            <strong>{{ $t['amount_words'] }}</strong><br>
                            {{ $amountWords ?? '-' }}
                        </td>
                    </tr>
                </table>
            </td>

            <td style="width: 54%;"></td>
        </tr>
    </table>

    <div class="footer">
        {{ $t['printed_at'] }}: {{ now()->format('Y-m-d H:i') }}
    </div>

</div>
</body>
</html>