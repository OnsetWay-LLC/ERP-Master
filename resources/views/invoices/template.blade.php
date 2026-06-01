<!DOCTYPE html>
<html dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}" lang="{{ $locale ?? 'ar' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $locale === 'en' ? 'Sales Invoice' : 'فاتورة مبيعات' }} - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'dejavu sans', sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }

        .invoice-box {
            max-width: 900px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            background-color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        /* ===== header meta table ===== */
        .header-table td {
            border: none;
            padding: 5px;
            vertical-align: top;
        }

        /* ===== title bar ===== */
        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* ===== items table ===== */
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .items-table th {
            background-color: #f8f8f8;
            font-weight: bold;
        }

        /* ===== totals ===== */
        .totals-table {
            width: 50%;
            @if($locale === 'en')
                float: right;
            @else
                float: left;
            @endif
        }
        .totals-table th,
        .totals-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .totals-table th {
            background-color: #f8f8f8;
            text-align: {{ $locale === 'en' ? 'right' : 'right' }};
        }
        .totals-table td {
            text-align: center;
        }

        .grand-total {
            font-weight: bold;
            font-size: 16px;
            background-color: #e8f4f8 !important;
        }

        /* ===== amount in words ===== */
        .in-words {
            clear: both;
            margin-top: 40px;
            padding: 15px;
            background-color: #f8f8f8;
            @if($locale === 'en')
                border-left: 4px solid #2c3e50;
            @else
                border-right: 4px solid #2c3e50;
            @endif
        }

        /* ===== action buttons (hidden on print/pdf) ===== */
        .actions {
            max-width: 900px;
            margin: 0 auto 20px auto;
            text-align: left;
            direction: ltr;
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            margin-right: 6px;
            font-family: 'dejavu sans', sans-serif;
        }
        .btn-dark   { background-color: #2c3e50; }
        .btn-print  { background-color: #4CAF50; }
        .btn-pdf    { background-color: #f44336; }

        @media print {
            .no-print { display: none !important; }
            .invoice-box { border: none; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="actions no-print">
        <a href="?locale=ar"   class="btn btn-dark">عربي</a>
        <a href="?locale=en"   class="btn btn-dark">English</a>
        <a href="?locale=both" class="btn btn-dark">AR / EN</a>
        <button onclick="window.print()" class="btn btn-print">
            {{ $locale === 'en' ? 'Print' : 'طباعة' }}
        </button>
        <a href="/api/sales-invoices/{{ $invoice->id }}/pdf?locale={{ $locale }}"
           class="btn btn-pdf">
            {{ $locale === 'en' ? 'Download PDF' : 'تحميل PDF' }}
        </a>
    </div>

    <div class="invoice-box">

        {{-- ========== TITLE ========== --}}
        <div class="header-title">
            @if($locale === 'ar')
                فاتورة مبيعات
            @elseif($locale === 'en')
                SALES INVOICE
            @else
                فاتورة مبيعات &nbsp;|&nbsp; SALES INVOICE
            @endif
        </div>

        {{-- ========== META ========== --}}
        <table class="header-table">
            <tr>
                {{-- الخلية الأولى (يمين في العربي، يسار في الانجليزي) --}}
                <td width="50%" style="text-align: {{ $locale === 'en' ? 'left' : 'right' }};">
                    <strong>
                        @if($locale === 'both') اسم الشركة | Company Name:
                        @elseif($locale === 'en') Company Name:
                        @else اسم الشركة:
                        @endif
                    </strong>
                 @if($locale === 'en')
    {{ $invoice->company?->name_en }}
@elseif($locale === 'ar')
    {{ $invoice->company?->name_ar }}
@else
    {{ $invoice->company?->name_ar }}
    <br>
    <small>{{ $invoice->company?->name_en }}</small>
@endif<br>

                    <strong>
                        @if($locale === 'both') رقم الفاتورة | Invoice No:
                        @elseif($locale === 'en') Invoice No:
                        @else رقم الفاتورة:
                        @endif
                    </strong>
                    {{ $invoice->invoice_number }}<br>

                    <strong>
                        @if($locale === 'both') تاريخ الفاتورة | Invoice Date:
                        @elseif($locale === 'en') Invoice Date:
                        @else تاريخ الفاتورة:
                        @endif
                    </strong>
                    {{ $invoice->posting_date }}
                </td>

                {{-- الخلية الثانية (يسار في العربي، يمين في الانجليزي) --}}
                <td width="50%" style="text-align: {{ $locale === 'en' ? 'right' : 'left' }};">
                    <strong>
                        @if($locale === 'both') العميل | Customer:
                        @elseif($locale === 'en') Customer:
                        @else العميل:
                        @endif
                    </strong>
                    {{ $invoice->customer->name_ar ?? $invoice->customer->name_en }}<br>

                    <strong>
                        @if($locale === 'both') تاريخ الاستحقاق | Due Date:
                        @elseif($locale === 'en') Due Date:
                        @else تاريخ الاستحقاق:
                        @endif
                    </strong>
                    {{ $invoice->payment_due_date ?? 'N/A' }}<br>

                    <strong>
                        @if($locale === 'both') طريقة الدفع | Payment Mode:
                        @elseif($locale === 'en') Payment Mode:
                        @else طريقة الدفع:
                        @endif
                    </strong>
                    {{ strtoupper($invoice->payment_mode) }}
                </td>
            </tr>
        </table>

        {{-- ========== ITEMS TABLE ========== --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">
                        @if($locale === 'both') الرقم<br>No.
                        @elseif($locale === 'en') No.
                        @else الرقم
                        @endif
                    </th>
                    <th width="40%">
                        @if($locale === 'both') اسم الصنف<br>Item Name
                        @elseif($locale === 'en') Item Name
                        @else اسم الصنف
                        @endif
                    </th>
                    <th width="15%">
                        @if($locale === 'both') الكمية<br>Qty
                        @elseif($locale === 'en') Qty
                        @else الكمية
                        @endif
                    </th>
                    <th width="20%">
                        @if($locale === 'both') السعر الافرادي<br>Unit Rate
                        @elseif($locale === 'en') Unit Rate
                        @else السعر الافرادي
                        @endif
                    </th>
                    <th width="20%">
                        @if($locale === 'both') المجموع<br>Amount
                        @elseif($locale === 'en') Amount
                        @else المجموع
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($locale === 'en')
                            {{ $item->item->name_en ?? $item->item->name_ar }}
                        @elseif($locale === 'ar')
                            {{ $item->item->name_ar ?? $item->item->name_en }}
                        @else
                            {{ $item->item->name_ar ?? $item->item->name_en }}
                            @if(($item->item->name_ar ?? '') && ($item->item->name_en ?? ''))
                                <br><small style="color:#666; font-size:11px;">{{ $item->item->name_en }}</small>
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

        {{-- ========== TOTALS ========== --}}
        <table class="totals-table">
            <tr>
                <th width="60%">
                    @if($locale === 'both') المجموع الفرعي | Sub Total
                    @elseif($locale === 'en') Sub Total
                    @else المجموع الفرعي
                    @endif
                </th>
                <td width="40%">{{ number_format($invoice->net_total, 2) }}</td>
            </tr>

            @if(($invoice->tax_total ?? 0) > 0)
            <tr>
                <th>
                    @if($locale === 'both') قيمة الضريبة | VAT Amount
                    @elseif($locale === 'en') VAT Amount
                    @else قيمة الضريبة
                    @endif
                </th>
                <td>{{ number_format($invoice->tax_total, 2) }}</td>
            </tr>
            @endif

            @if(($invoice->fees_total ?? 0) > 0)
            <tr>
                <th>
                    @if($locale === 'both') الرسوم الإضافية | Extra Fees
                    @elseif($locale === 'en') Extra Fees
                    @else الرسوم الإضافية
                    @endif
                </th>
                <td>{{ number_format($invoice->fees_total, 2) }}</td>
            </tr>
            @endif

            @if(($invoice->discount_amount ?? 0) > 0)
            <tr>
                <th>
                    @if($locale === 'both') قيمة الخصم | Discount
                    @elseif($locale === 'en') Discount
                    @else قيمة الخصم
                    @endif
                </th>
                <td style="color:red;">-{{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif

            <tr>
                <th class="grand-total">
                    @if($locale === 'both') الإجمالي الكلي | Grand Total
                    @elseif($locale === 'en') Grand Total
                    @else الإجمالي الكلي
                    @endif
                </th>
                <td class="grand-total">{{ number_format($invoice->grand_total, 2) }}</td>
            </tr>
        </table>

        {{-- ========== AMOUNT IN WORDS ========== --}}
        <div class="in-words">
            <strong>
                @if($locale === 'both') المبلغ كتابة | Amount in Words:
                @elseif($locale === 'en') Amount in Words:
                @else المبلغ كتابة:
                @endif
            </strong><br>
            {{ $amountInWords }}
        </div>

    </div>
</body>
</html>