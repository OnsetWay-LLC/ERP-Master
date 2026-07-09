<!DOCTYPE html>
<html dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}" lang="{{ $locale ?? 'ar' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $locale === 'en' ? 'Sales Invoice' : 'فاتورة مبيعات' }} - {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'dejavu sans', sans-serif;
            background: #e8f1fb;
            color: #042c53;
            padding: 24px;
            font-size: 13px;
        }

        /* ── Action bar ── */
        .actions {
            max-width: 720px;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            gap: 6px;
            direction: ltr;
        }

        .btn {
            padding: 6px 14px;
            font-size: 12px;
            border: 0.5px solid #85b7eb;
            border-radius: 8px;
            cursor: pointer;
            background: #fff;
            color: #185fa5;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-family: 'dejavu sans', sans-serif;
        }

        .btn:hover { background: #e6f1fb; }

        .btn-pdf {
            margin-left: auto;
            background: #fff;
            color: #a32d2d;
            border-color: #f09595;
        }

        .btn-pdf:hover { background: #fcebeb; }

        /* ── Invoice box ── */
        .invoice-box {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border: 0.5px solid #85b7eb;
            border-radius: 14px;
            overflow: hidden;
        }

        /* ── Top blue banner ── */
        .inv-banner {
            background: #185fa5;
            padding: 20px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .inv-title {
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 0.04em;
        }

        .inv-title-sub {
            font-size: 11px;
            color: #b5d4f4;
            margin-top: 3px;
        }

        .inv-number-badge {
            background: rgba(255,255,255,0.15);
            border: 0.5px solid rgba(255,255,255,0.35);
            border-radius: 8px;
            padding: 8px 18px;
            text-align: center;
        }

        .inv-number-label {
            font-size: 10px;
            color: #b5d4f4;
            letter-spacing: 0.05em;
        }

        .inv-number-val {
            font-size: 15px;
            font-weight: bold;
            color: #fff;
            margin-top: 2px;
        }

        /* ── Header info ── */
        .inv-header {
            background: #f0f7ff;
            border-bottom: 0.5px solid #b5d4f4;
            padding: 18px 28px;
        }

        .inv-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .inv-meta-card {
            background: #fff;
            border: 0.5px solid #b5d4f4;
            border-radius: 8px;
            overflow: hidden;
        }

        .inv-meta-card-header {
            background: #185fa5;
            padding: 6px 14px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 0.06em;
        }

        .inv-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 14px;
            border-bottom: 0.5px solid #e6f1fb;
        }

        .inv-meta-row:last-child { border-bottom: none; }
        .inv-meta-key { font-size: 11px; color: #378add; }
        .inv-meta-val { font-size: 12px; font-weight: bold; color: #042c53; }

        /* ── Status badge ── */
        .inv-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #eaf3de;
            border: 0.5px solid #97c459;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: bold;
            color: #3b6d11;
            margin-bottom: 14px;
        }

        .inv-status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #3b6d11;
        }

        /* ── Section title ── */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #378add;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 16px 28px 8px;
        }

        /* ── Items table ── */
        .table-wrap { padding: 0 28px; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 0.5px solid #b5d4f4;
            border-radius: 8px;
            overflow: hidden;
        }

        .items-table thead th {
            background: #185fa5;
            font-size: 11px;
            font-weight: bold;
            color: #fff;
            padding: 10px 14px;
            text-align: center;
        }

        .items-table thead th:nth-child(2) {
            text-align: {{ $locale === 'en' ? 'left' : 'right' }};
        }

        .items-table tbody td {
            padding: 11px 14px;
            font-size: 12px;
            border-bottom: 0.5px solid #e6f1fb;
            text-align: center;
            vertical-align: middle;
            color: #042c53;
        }

        .items-table tbody tr:last-child td { border-bottom: none; }
        .items-table tbody tr:hover td { background: #f0f7ff; }

        .items-table tbody td.td-item {
            text-align: {{ $locale === 'en' ? 'left' : 'right' }};
        }

        .item-name-en {
            font-size: 11px;
            color: #378add;
            margin-top: 2px;
        }

        /* ── Footer ── */
        .inv-footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 16px 28px 24px;
            border-top: 0.5px solid #b5d4f4;
            margin-top: 12px;
        }

        .inv-words {
            background: #f0f7ff;
            border: 0.5px solid #b5d4f4;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .inv-words-label {
            font-size: 10px;
            font-weight: bold;
            color: #378add;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .inv-words-text {
            font-size: 12px;
            font-weight: bold;
            color: #042c53;
            line-height: 1.6;
        }

        .inv-totals {
            border: 0.5px solid #b5d4f4;
            border-radius: 8px;
            overflow: hidden;
        }

        .tot-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 16px;
            border-bottom: 0.5px solid #e6f1fb;
            font-size: 12px;
        }

        .tot-row:last-child { border-bottom: none; }
        .tot-key { color: #378add; }
        .tot-val { font-weight: bold; color: #042c53; }
        .tot-val.discount { color: #a32d2d; }

        .grand-row { background: #185fa5; }
        .grand-row .tot-key { font-size: 13px; font-weight: bold; color: #b5d4f4; }
        .grand-row .tot-val { font-size: 14px; font-weight: bold; color: #fff; }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .invoice-box { border: none; }
        }
        .company-logo {
    max-height: 55px;
    max-width: 130px;
    margin-bottom: 8px;
}

.company-report-header {
    font-size: 10px;
    color: #dbeafe;
    margin-top: 5px;
    line-height: 1.5;
}

.invoice-custom-footer {
    margin: 0 28px 12px;
    padding: 12px 16px;
    background: #f0f7ff;
    border: 0.5px solid #b5d4f4;
    border-radius: 8px;
    font-size: 11px;
    color: #042c53;
    line-height: 1.7;
    text-align: center;
}

.invoice-system-footer {
    text-align: center;
    padding: 0 28px 22px;
}

.invoice-system-footer img {
    width: 100%;
    max-width: 620px;
    height: auto;
}
.inv-banner {
    background: #1764a7;
    color: white;
    padding: 18px 22px 15px;
    text-align: center;
}

.inv-banner > div {
    width: 100%;
    text-align: center;
}

.company-logo {
    display: block;
    max-height: 55px;
    max-width: 130px;
    margin: 0 auto 8px auto;
}

.inv-title {
    text-align: center;
}

.inv-title-main {
    text-align: center;
}

.inv-title-sub {
    text-align: center;
}

.company-report-header {
    text-align: center;
    margin-top: 5px;
}
.report-footer {
    margin: 15px 28px 10px;
    padding: 10px 15px;
    text-align: center;
    font-size: 10px;
    line-height: 1.6;
    color: #374151;
    border-top: 1px solid #d1d5db;
}

.system-footer {
    text-align: center;
    margin-top: 8px;
    padding: 5px 28px 15px;
}

.system-footer img {
    width: 100%;
    max-width: 650px;
    height: auto;
}
    </style>
</head>
<body>

{{-- ── Action bar ── --}}
<div class="actions no-print">
    <a href="?locale=ar"   class="btn">عربي</a>
    <a href="?locale=en"   class="btn">English</a>
    <a href="?locale=both" class="btn">AR / EN</a>

    <button onclick="window.print()" class="btn">
        {{ $locale === 'en' ? 'Print' : 'طباعة' }}
    </button>

    <a href="/api/sales-invoices/{{ $invoice->id }}/pdf?locale={{ $locale }}" class="btn btn-pdf">
        {{ $locale === 'en' ? 'Download PDF' : 'تحميل PDF' }}
    </a>
</div>

<div class="invoice-box">

    {{-- ── Blue banner ── --}}
   <div class="inv-banner">
    <div>
        @if($invoice->company?->logo)
            <img
                class="company-logo"
                src="{{ public_path($invoice->company->logo) }}"
                alt="Company Logo"
            >
        @endif

        <div class="inv-title">
            @if($invoice->company?->report_header)
    <div class="company-report-header">
        {!! nl2br(e($invoice->company->report_header)) !!}
    </div>
@endif
                @if($locale === 'ar') فاتورة مبيعات
                @elseif($locale === 'en') SALES INVOICE
                @else فاتورة مبيعات &nbsp;|&nbsp; SALES INVOICE
                @endif
            </div>
            <div class="inv-title-sub">
                @if($locale === 'en')
                    {{ $invoice->company?->name_en }}
                @elseif($locale === 'ar')
                    {{ $invoice->company?->name_ar }}
                @else
                    {{ $invoice->company?->name_ar }} &nbsp;|&nbsp; {{ $invoice->company?->name_en }}
                @endif
            </div>
        </div>
        <div class="inv-number-badge">
            <div class="inv-number-label">
                @if($locale === 'both') رقم الفاتورة | Invoice No
                @elseif($locale === 'en') Invoice No
                @else رقم الفاتورة @endif
            </div>
            <div class="inv-number-val">{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    {{-- ── Meta info ── --}}
    <div class="inv-header">
        <div class="inv-status">
            <span class="inv-status-dot"></span>
            {{ $locale === 'en' ? 'Paid' : 'مدفوع' }}
        </div>

        <div class="inv-meta">
            {{-- Card 1: Invoice info --}}
            <div class="inv-meta-card">
                <div class="inv-meta-card-header">
                    @if($locale === 'both') بيانات الفاتورة | Invoice Details
                    @elseif($locale === 'en') INVOICE DETAILS
                    @else بيانات الفاتورة @endif
                </div>
                <div class="inv-meta-row">
                    <span class="inv-meta-key">
                        @if($locale === 'both') تاريخ الفاتورة | Invoice Date
                        @elseif($locale === 'en') Invoice Date
                        @else تاريخ الفاتورة @endif
                    </span>
                    <span class="inv-meta-val">{{ $invoice->posting_date }}</span>
                </div>
                <div class="inv-meta-row">
                    <span class="inv-meta-key">
                        @if($locale === 'both') تاريخ الاستحقاق | Due Date
                        @elseif($locale === 'en') Due Date
                        @else تاريخ الاستحقاق @endif
                    </span>
                    <span class="inv-meta-val">{{ $invoice->payment_due_date ?? 'N/A' }}</span>
                </div>
                <div class="inv-meta-row">
                    <span class="inv-meta-key">
                        @if($locale === 'both') طريقة الدفع | Payment Mode
                        @elseif($locale === 'en') Payment Mode
                        @else طريقة الدفع @endif
                    </span>
                    <span class="inv-meta-val">{{ strtoupper($invoice->payment_mode) }}</span>
                </div>
            </div>

            {{-- Card 2: Customer info --}}
            <div class="inv-meta-card">
                <div class="inv-meta-card-header">
                    @if($locale === 'both') بيانات العميل | Customer Details
                    @elseif($locale === 'en') CUSTOMER DETAILS
                    @else بيانات العميل @endif
                </div>
                <div class="inv-meta-row">
                    <span class="inv-meta-key">
                        @if($locale === 'both') العميل | Customer
                        @elseif($locale === 'en') Customer
                        @else العميل @endif
                    </span>
                    <span class="inv-meta-val">
                        {{ $locale === 'en'
                            ? ($invoice->customer?->name_en ?? $invoice->customer?->name_ar)
                            : ($invoice->customer?->name_ar ?? $invoice->customer?->name_en) }}
                    </span>
                </div>
                <div class="inv-meta-row">
                    <span class="inv-meta-key">
                        @if($locale === 'both') مندوب المبيعات | Sales Person
                        @elseif($locale === 'en') Sales Person
                        @else مندوب المبيعات @endif
                    </span>
                    <span class="inv-meta-val">
                        @if($locale === 'en')
                            {{ $invoice->salesPerson?->sales_person_name_en ?? 'N/A' }}
                        @else
                            {{ $invoice->salesPerson?->sales_person_name_ar ?? 'غير محدد' }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Items ── --}}
    <div class="section-title">
        @if($locale === 'both') بنود الفاتورة | Invoice Items
        @elseif($locale === 'en') Invoice Items
        @else بنود الفاتورة @endif
    </div>

    <div class="table-wrap">
        <table class="items-table">
            <thead>
            <tr>
                <th width="5%">#</th>
                <th width="40%">
                    @if($locale === 'both') اسم الصنف | Item Name
                    @elseif($locale === 'en') Item Name
                    @else اسم الصنف @endif
                </th>
                <th width="15%">
                    @if($locale === 'both') الكمية | Qty
                    @elseif($locale === 'en') Qty
                    @else الكمية @endif
                </th>
                <th width="20%">
                    @if($locale === 'both') السعر الافرادي | Unit Rate
                    @elseif($locale === 'en') Unit Rate
                    @else السعر الافرادي @endif
                </th>
                <th width="20%">
                    @if($locale === 'both') المجموع | Amount
                    @elseif($locale === 'en') Amount
                    @else المجموع @endif
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="td-item">
                        @if($locale === 'en')
                            {{ $item->item?->name_en ?? $item->item?->name_ar }}
                        @elseif($locale === 'ar')
                            {{ $item->item?->name_ar ?? $item->item?->name_en }}
                        @else
                            {{ $item->item?->name_ar ?? $item->item?->name_en }}
                            @if($item->item?->name_en)
                                <div class="item-name-en">{{ $item->item?->name_en }}</div>
                            @endif
                        @endif
                    </td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->rate, 2) }}</td>
                    <td>{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Footer ── --}}
    <div class="inv-footer">
        <div class="inv-words">
            <div class="inv-words-label">
                @if($locale === 'both') المبلغ كتابةً | Amount in Words
                @elseif($locale === 'en') Amount in Words
                @else المبلغ كتابةً @endif
            </div>
            <div class="inv-words-text">{{ $amountInWords }}</div>
        </div>

        <div class="inv-totals">
            <div class="tot-row">
                <span class="tot-key">
                    @if($locale === 'both') المجموع الفرعي | Sub Total
                    @elseif($locale === 'en') Sub Total
                    @else المجموع الفرعي @endif
                </span>
                <span class="tot-val">{{ number_format($invoice->net_total, 2) }}</span>
            </div>

            @if(($invoice->tax_total ?? 0) > 0)
            <div class="tot-row">
                <span class="tot-key">
                    @if($locale === 'both') قيمة الضريبة | VAT Amount
                    @elseif($locale === 'en') VAT Amount
                    @else قيمة الضريبة @endif
                </span>
                <span class="tot-val">{{ number_format($invoice->tax_total, 2) }}</span>
            </div>
            @endif

            @if(($invoice->fees_total ?? 0) > 0)
            <div class="tot-row">
                <span class="tot-key">
                    @if($locale === 'both') الرسوم الإضافية | Extra Fees
                    @elseif($locale === 'en') Extra Fees
                    @else الرسوم الإضافية @endif
                </span>
                <span class="tot-val">{{ number_format($invoice->fees_total, 2) }}</span>
            </div>
            @endif

            @if(($invoice->discount_amount ?? 0) > 0)
            <div class="tot-row">
                <span class="tot-key">
                    @if($locale === 'both') قيمة الخصم | Discount
                    @elseif($locale === 'en') Discount
                    @else قيمة الخصم @endif
                </span>
                <span class="tot-val discount">-{{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif

            <div class="tot-row grand-row">
                <span class="tot-key">
                    @if($locale === 'both') الإجمالي الكلي | Grand Total
                    @elseif($locale === 'en') Grand Total
                    @else الإجمالي الكلي @endif
                </span>
                <span class="tot-val">{{ number_format($invoice->grand_total, 2) }}</span>
            </div>
        </div>
    </div>

{{-- Report Footer --}}
@if(!empty($invoice->company?->report_footer))
    <div class="report-footer">
        {!! nl2br(e($invoice->company->report_footer)) !!}
    </div>
@endif

{{-- System Footer --}}
@php
    $systemFooterImage = $locale === 'en'
        ? public_path('images/reports/system-footer-en.png')
        : public_path('images/reports/system-footer-ar.png');
@endphp

<div class="system-footer">
    <img src="{{ $systemFooterImage }}" alt="System Footer">
</div>



</div>
</body>
</html>
