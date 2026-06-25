<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #1a2a3a;
            background: #f0f7ff;
            padding: 28px;
        }

        /* ── HEADER ── */
        .header {
            background: linear-gradient(135deg, #0D2E5A 60%, #1565C0 100%);
            color: white;
            padding: 0;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
            box-shadow: 0 4px 18px rgba(13,46,90,0.18);
        }

        .header-inner {
            padding: 22px 30px 18px;
            position: relative;
            z-index: 2;
        }

        .header::after {
            content: '';
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 38%;
            background: linear-gradient(135deg, transparent 30%, rgba(33,150,243,0.22) 100%);
            clip-path: polygon(18% 0, 100% 0, 100% 100%, 0% 100%);
            z-index: 1;
        }

        .header h2 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .header h4 {
            font-size: 13px;
            font-weight: 400;
            opacity: 0.78;
            letter-spacing: 0.3px;
        }

        .header-badge {
            position: absolute;
            right: 30px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 3;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.22);
            border-radius: 8px;
            padding: 8px 18px;
            text-align: center;
        }

        .header-badge span {
            display: block;
            font-size: 9px;
            opacity: 0.7;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-badge strong {
            font-size: 13px;
        }

        /* ── META INFO ── */
        .meta-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
        }

        .meta-card {
            background: white;
            border-radius: 7px;
            padding: 10px 16px;
            border-left: 3px solid #2196F3;
            box-shadow: 0 1px 6px rgba(13,46,90,0.07);
            flex: 1;
        }

        .meta-card .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #1565C0;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .meta-card .value {
            font-size: 12px;
            font-weight: 600;
            color: #0D2E5A;
        }

        /* ── MAIN TABLE ── */
        .table-wrap {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 14px rgba(13,46,90,0.09);
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #0D2E5A;
        }

        th {
            color: white;
            padding: 11px 10px;
            font-size: 10px;
            font-weight: 600;
            text-align: center;
            letter-spacing: 0.3px;
            border: none;
        }

        th .title {
            display: block;
            font-size: 8.5px;
            opacity: 0.65;
            font-weight: 400;
            margin-top: 2px;
            letter-spacing: 0.2px;
        }

        tbody tr {
            border-bottom: 1px solid #e8f0f9;
            transition: background 0.15s;
        }

        tbody tr:nth-child(even) {
            background: #f5f9ff;
        }

        tbody tr:hover {
            background: #deeeff;
        }

        td {
            padding: 9px 10px;
            text-align: center;
            font-size: 10.5px;
            color: #1a2a3a;
            border: none;
        }

        .number {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-family: 'Courier New', monospace;
            font-size: 10.5px;
        }

        .no-data td {
            color: #7a9bbf;
            padding: 24px;
            font-style: italic;
        }

        /* ── TOTALS TABLE ── */
        .totals-wrap {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 14px rgba(13,46,90,0.09);
            border-top: 3px solid #1565C0;
        }

        .totals-wrap table thead tr {
            background: #1565C0;
        }

        .totals-wrap table tbody td {
            font-size: 12px;
            font-weight: 700;
            padding: 12px 14px;
            font-variant-numeric: tabular-nums;
            font-family: 'Courier New', monospace;
            color: #0D2E5A;
        }

        .profit-positive {
            color: #0a6e2e;
        }

    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="header-inner">
        <h2>Gross Profit Report</h2>
        <h4>تقرير الربح الإجمالي</h4>
    </div>
    <div class="header-badge">
        <span>Report Type</span>
        <strong>{{ ucwords(str_replace('_',' ',$report['filter_by'])) }}</strong>
    </div>
</div>

<!-- META BAR -->
<div class="meta-bar">
    <div class="meta-card">
        <div class="label">From | من</div>
        <div class="value">{{ $report['from_date'] }}</div>
    </div>
    <div class="meta-card">
        <div class="label">To | إلى</div>
        <div class="value">{{ $report['to_date'] }}</div>
    </div>
    <div class="meta-card">
        <div class="label">Filter | الفلترة</div>
        <div class="value">{{ ucwords(str_replace('_',' ',$report['filter_by'])) }}</div>
    </div>
</div>

<!-- MAIN TABLE -->
<div class="table-wrap">
<table>
<thead>
<tr>

@if($report['filter_by']=='sales_invoice')
<th>Sales Invoice <span class="title">فاتورة المبيعات</span></th>
<th>Customer <span class="title">العميل</span></th>
<th>Posting Date <span class="title">تاريخ الترحيل</span></th>
<th>Item Group <span class="title">مجموعة الصنف</span></th>
<th>Warehouse <span class="title">المستودع</span></th>
@endif

@if($report['filter_by']=='item_code')
<th>Item Code <span class="title">رمز الصنف</span></th>
<th>Item Name <span class="title">اسم الصنف</span></th>
@endif

@if($report['filter_by']=='item_group')
<th>Item Group <span class="title">مجموعة الأصناف</span></th>
@endif

@if($report['filter_by']=='warehouse')
<th>Warehouse <span class="title">المستودع</span></th>
@endif

@if($report['filter_by']=='customer')
<th>Customer <span class="title">العميل</span></th>
@endif

@if($report['filter_by']=='sales_person')
<th>Sales Person <span class="title">مندوب المبيعات</span></th>
<th>Allocated Amount <span class="title">المبلغ المخصص</span></th>
@endif

<th>QTY</th>
<th>Selling Rate</th>
<th>Valuation Rate</th>
<th>Selling Amount</th>
<th>Buying Amount</th>
<th>Gross Profit</th>
<th>Gross Profit %</th>

</tr>
</thead>
<tbody>

@forelse($report['rows'] as $row)
<tr>

@if($report['filter_by']=='sales_invoice')
<td>{{ $row['sales_invoice'] }}</td>
<td>{{ $row['customer_name_en'] ?? $row['customer_name_ar'] }}</td>
<td>{{ $row['posting_date'] }}</td>
<td>{{ $row['item_group_name_en'] ?? $row['item_group_name_ar'] }}</td>
<td>{{ $row['warehouse_name_en'] ?? $row['warehouse_name_ar'] }}</td>
@endif

@if($report['filter_by']=='item_code')
<td>{{ $row['item_code'] }}</td>
<td>{{ $row['item_name_en'] ?? $row['item_name_ar'] }}</td>
@endif

@if($report['filter_by']=='item_group')
<td>{{ $row['item_group_name_en'] ?? $row['item_group_name_ar'] }}</td>
@endif

@if($report['filter_by']=='warehouse')
<td>{{ $row['warehouse_name_en'] ?? $row['warehouse_name_ar'] }}</td>
@endif

@if($report['filter_by']=='customer')
<td>{{ $row['customer_name_en'] ?? $row['customer_name_ar'] }}</td>
@endif

@if($report['filter_by']=='sales_person')
<td>{{ $row['sales_person_name_en'] ?? $row['sales_person_name_ar'] }}</td>
<td class="number">{{ number_format($row['allocated_amount'], 2) }}</td>
@endif

<td class="number">{{ number_format($row['qty'], 2) }}</td>
<td class="number">{{ number_format($row['selling_rate'], 2) }}</td>
<td class="number">{{ number_format($row['valuation_rate'], 2) }}</td>
<td class="number">{{ number_format($row['selling_amount'], 2) }}</td>
<td class="number">{{ number_format($row['buying_amount'], 2) }}</td>
<td class="number profit-positive">{{ number_format($row['gross_profit'], 2) }}</td>
<td class="number">{{ number_format($row['gross_profit_percent'], 2) }}%</td>

</tr>
@empty
<tr class="no-data">
    <td colspan="15">No data found | لا توجد بيانات</td>
</tr>
@endforelse

</tbody>
</table>
</div>

<!-- TOTALS TABLE -->
<div class="totals-wrap">
<table>
<thead>
<tr>
    <th>Total QTY <span class="title">إجمالي الكمية</span></th>
    <th>Total Selling <span class="title">إجمالي البيع</span></th>
    <th>Total Buying <span class="title">إجمالي التكلفة</span></th>
    <th>Total Gross Profit <span class="title">إجمالي الربح</span></th>
    <th>Gross Profit % <span class="title">نسبة الربح</span></th>
</tr>
</thead>
<tbody>
<tr>
    <td class="number">{{ number_format($report['totals']['qty'], 2) }}</td>
    <td class="number">{{ number_format($report['totals']['selling_amount'], 2) }}</td>
    <td class="number">{{ number_format($report['totals']['buying_amount'], 2) }}</td>
    <td class="number profit-positive">{{ number_format($report['totals']['gross_profit'], 2) }}</td>
    <td class="number">{{ number_format($report['totals']['gross_profit_percent'], 2) }}%</td>
</tr>
</tbody>
</table>
</div>

</body>
</html>