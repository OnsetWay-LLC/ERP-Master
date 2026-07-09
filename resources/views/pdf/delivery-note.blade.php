<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $locale === 'ar' ? 'إشعار تسليم' : 'Delivery Note' }}</title>

    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        .company-header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #d1d5db;
        }

        .company-logo img {
            max-height: 60px;
            max-width: 150px;
        }

        .company-name {
            font-size: 13px;
            font-weight: bold;
            margin-top: 4px;
        }

        .company-info {
            font-size: 9px;
            color: #4b5563;
            margin-top: 3px;
        }

        .custom-report-header {
            font-size: 9px;
            color: #374151;
            margin-top: 5px;
            white-space: pre-line;
        }

        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .info-table,
        .items-table,
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .info-table td,
        .items-table th,
        .items-table td,
        .totals-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
        }

        .label {
            background: #f3f4f6;
            font-weight: bold;
            width: 20%;
        }

        .items-table th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }

        .items-table td {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .total-row {
            font-weight: bold;
            background: #f9fafb;
        }
    </style>
</head>

<body>

<div class="company-header">
    @if($company->logo)
        <div class="company-logo">
            <img src="{{ public_path($company->logo) }}">
        </div>
    @endif

    <div class="company-name">
        {{ $locale === 'ar'
            ? ($company->name_ar ?? $company->name_en)
            : ($company->name_en ?? $company->name_ar)
        }}
    </div>

    <div class="company-info">
        @if($company->email ?? null)
            {{ $company->email }}
        @endif

        @if($company->phone ?? null)
            | {{ $company->phone }}
        @endif

        @if($company->country ?? null)
            | {{ $company->country }}
        @endif
    </div>

    @if($company->report_header)
        <div class="custom-report-header">
            {!! nl2br(e($company->report_header)) !!}
        </div>
    @endif
</div>

<div class="report-title">
    {{ $locale === 'ar' ? 'إشعار تسليم' : 'Delivery Note' }}
</div>

<table class="info-table">
    <tr>
        <td class="label">{{ $locale === 'ar' ? 'رقم إشعار التسليم' : 'Delivery Note No.' }}</td>
        <td>{{ $deliveryNote->delivery_note_number }}</td>

        <td class="label">{{ $locale === 'ar' ? 'الحالة' : 'Status' }}</td>
        <td>{{ $deliveryNote->status }}</td>
    </tr>

    <tr>
        <td class="label">{{ $locale === 'ar' ? 'التاريخ' : 'Posting Date' }}</td>
        <td>{{ $deliveryNote->posting_date }}</td>

        <td class="label">{{ $locale === 'ar' ? 'الوقت' : 'Posting Time' }}</td>
        <td>{{ $deliveryNote->posting_time }}</td>
    </tr>

    <tr>
        <td class="label">{{ $locale === 'ar' ? 'العميل' : 'Customer' }}</td>
        <td>
            {{ $locale === 'ar'
                ? ($deliveryNote->customer?->name_ar ?? $deliveryNote->customer?->name_en)
                : ($deliveryNote->customer?->name_en ?? $deliveryNote->customer?->name_ar)
            }}
        </td>

        <td class="label">{{ $locale === 'ar' ? 'أمر البيع' : 'Sales Order' }}</td>
        <td>{{ $deliveryNote->salesOrder?->series ?? $deliveryNote->salesOrder?->sales_order_number ?? '—' }}</td>
    </tr>

    <tr>
        <td class="label">{{ $locale === 'ar' ? 'قائمة التحضير' : 'Pick List' }}</td>
        <td>{{ $deliveryNote->pickList?->series ?? '—' }}</td>

        <td class="label">{{ $locale === 'ar' ? 'إجمالي الكمية' : 'Total Qty' }}</td>
        <td>{{ number_format($deliveryNote->total_qty, 2) }}</td>
    </tr>
</table>

<table class="items-table">
    <thead>
    <tr>
        <th>#</th>
        <th>{{ $locale === 'ar' ? 'كود الصنف' : 'Item Code' }}</th>
        <th>{{ $locale === 'ar' ? 'اسم الصنف' : 'Item Name' }}</th>
        <th>{{ $locale === 'ar' ? 'المستودع' : 'Warehouse' }}</th>
        <th>{{ $locale === 'ar' ? 'الكمية' : 'Qty' }}</th>
        <th>{{ $locale === 'ar' ? 'السعر' : 'Rate' }}</th>
        <th>{{ $locale === 'ar' ? 'الإجمالي' : 'Amount' }}</th>
    </tr>
    </thead>

    <tbody>
    @foreach($deliveryNote->items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->item_code }}</td>
            <td class="text-left">
                {{ $locale === 'ar'
                    ? ($item->item_name_ar ?? $item->item_name_en)
                    : ($item->item_name_en ?? $item->item_name_ar)
                }}
            </td>
            <td>
                {{ $locale === 'ar'
                    ? ($item->warehouse?->name_ar ?? $item->warehouse?->name_en)
                    : ($item->warehouse?->name_en ?? $item->warehouse?->name_ar)
                }}
            </td>
            <td>{{ number_format($item->quantity, 2) }}</td>
            <td class="amount">{{ number_format($item->rate, 2) }}</td>
            <td class="amount">{{ number_format($item->amount, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="totals-table">
    <tr>
        <td class="label">{{ $locale === 'ar' ? 'الصافي' : 'Net Total' }}</td>
        <td class="amount">{{ number_format($deliveryNote->net_total, 2) }}</td>
    </tr>

    <tr>
        <td class="label">{{ $locale === 'ar' ? 'الضريبة' : 'Tax Total' }}</td>
        <td class="amount">{{ number_format($deliveryNote->tax_total, 2) }}</td>
    </tr>

    <tr>
        <td class="label">{{ $locale === 'ar' ? 'الرسوم' : 'Fees Total' }}</td>
        <td class="amount">{{ number_format($deliveryNote->fees_total, 2) }}</td>
    </tr>

    <tr>
        <td class="label">{{ $locale === 'ar' ? 'الخصم' : 'Discount' }}</td>
        <td class="amount">{{ number_format($deliveryNote->discount_amount, 2) }}</td>
    </tr>

    <tr class="total-row">
        <td class="label">{{ $locale === 'ar' ? 'الإجمالي الكلي' : 'Grand Total' }}</td>
        <td class="amount">{{ number_format($deliveryNote->grand_total, 2) }}</td>
    </tr>
</table>

</body>
</html>