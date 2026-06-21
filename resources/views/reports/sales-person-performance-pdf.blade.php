<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: {{ $locale === 'ar' ? "'IBM Plex Sans Arabic'" : "'IBM Plex Sans'" }}, sans-serif;
            direction: {{ $locale === 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ $locale === 'ar' ? 'right' : 'left' }};
            background: #ffffff;
            color: #001D39;
            padding: 2rem;
            font-size: 11px;
        }

        /* ── Header ── */
        .report-header {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #0A4174;
        }

        .report-header h1 {
            font-size: 20px;
            font-weight: 600;
            color: #001D39;
            margin-bottom: 3px;
        }

        .report-header p {
            font-size: 11px;
            color: #49769F;
        }

        /* ── Filters ── */
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 1.2rem;
        }

        .filter-pill {
            background: #f4f8fc;
            border: 0.5px solid #BDD8E9;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .filter-pill .label {
            font-size: 9px;
            color: #49769F;
            margin-bottom: 2px;
            letter-spacing: 0.4px;
        }

        .filter-pill .value {
            font-size: 12px;
            font-weight: 500;
            color: #0A4174;
        }

        /* ── Summary cards ── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 1.2rem;
        }

        .summary-card {
            background: #0A4174;
            border-radius: 10px;
            padding: 12px 14px;
            text-align: center;
        }

        .summary-card .s-label {
            font-size: 9px;
            color: #7BBDE8;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }

        .summary-card .s-value {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
        }

        .summary-card .s-unit {
            font-size: 9px;
            color: #6EA2B3;
            margin-top: 2px;
        }

        /* ── Table ── */
        .table-wrap {
            border: 1px solid #BDD8E9;
            border-radius: 10px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        thead tr {
            background: #0A4174;
        }

        thead th {
            padding: 10px 8px;
            color: #BDD8E9;
            font-weight: 500;
            font-size: 10px;
            letter-spacing: 0.2px;
            text-align: center;
        }

        tbody tr {
            border-bottom: 0.5px solid #BDD8E9;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:nth-child(even) {
            background: #f4f8fc;
        }

        tbody td {
            padding: 9px 8px;
            text-align: center;
            color: #001D39;
        }

        .td-name {
            font-weight: 500;
            color: #0A4174;
            text-align: {{ $locale === 'ar' ? 'right' : 'left' }};
            padding-{{ $locale === 'ar' ? 'right' : 'left' }}: 12px !important;
        }

        .td-index {
            color: #49769F;
            font-size: 10px;
        }

        /* ── Achievement badges ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 500;
        }

        .badge-high {
            background: #e8f3fb;
            color: #0A4174;
            border: 0.5px solid #7BBDE8;
        }

        .badge-mid {
            background: #f4f8fc;
            color: #49769F;
            border: 0.5px solid #BDD8E9;
        }

        /* ── No data ── */
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #49769F;
        }

        /* ── Print ── */
        @media print {
            body { padding: 1rem; }
            .table-wrap { border-radius: 0; }
        }
    </style>
</head>

<body>

{{-- Header --}}
<div class="report-header">
    <h1>
        {{ $locale === 'ar' ? 'تقرير أداء مندوبي المبيعات' : 'Sales Person Performance Report' }}
    </h1>
    <p>
        {{ $locale === 'ar' ? 'Sales Person Performance Report' : 'تقرير أداء مندوبي المبيعات' }}
    </p>
</div>

{{-- Filters --}}
<div class="filters-grid">
    <div class="filter-pill">
        <div class="label">{{ $locale === 'ar' ? 'من تاريخ' : 'From Date' }}</div>
        <div class="value">{{ $filters['from_date'] }}</div>
    </div>
    <div class="filter-pill">
        <div class="label">{{ $locale === 'ar' ? 'إلى تاريخ' : 'To Date' }}</div>
        <div class="value">{{ $filters['to_date'] }}</div>
    </div>
    <div class="filter-pill">
        <div class="label">{{ $locale === 'ar' ? 'مندوب المبيعات' : 'Sales Person' }}</div>
        <div class="value">{{ $filters['sales_person_id'] ?? ($locale === 'ar' ? 'الكل' : 'All') }}</div>
    </div>
    <div class="filter-pill">
        <div class="label">{{ $locale === 'ar' ? 'مجموعة الأصناف' : 'Item Group' }}</div>
        <div class="value">{{ $filters['item_group_id'] ?? ($locale === 'ar' ? 'الكل' : 'All') }}</div>
    </div>
    <div class="filter-pill">
        <div class="label">{{ $locale === 'ar' ? 'المستودع' : 'Warehouse' }}</div>
        <div class="value">{{ $filters['warehouse_id'] ?? ($locale === 'ar' ? 'الكل' : 'All') }}</div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="summary-grid">
    <div class="summary-card">
        <div class="s-label">{{ $locale === 'ar' ? 'إجمالي الهدف' : 'Total Period Target' }}</div>
        <div class="s-value">{{ number_format($summary['total_period_target'], 2) }}</div>
    </div>
    <div class="summary-card">
        <div class="s-label">{{ $locale === 'ar' ? 'إجمالي المبيعات' : 'Total Actual Sales' }}</div>
        <div class="s-value">{{ number_format($summary['total_actual_sales'], 2) }}</div>
    </div>
    <div class="summary-card">
        <div class="s-label">{{ $locale === 'ar' ? 'إجمالي العمولات' : 'Total Commission' }}</div>
        <div class="s-value">{{ number_format($summary['total_commission_amount'], 2) }}</div>
    </div>
    <div class="summary-card">
        <div class="s-label">{{ $locale === 'ar' ? 'عدد الفواتير' : 'Invoice Count' }}</div>
        <div class="s-value">{{ $summary['total_invoice_count'] }}</div>
    </div>
</div>

{{-- Main Table --}}
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th style="text-align: {{ $locale === 'ar' ? 'right' : 'left' }}; padding-{{ $locale === 'ar' ? 'right' : 'left' }}: 12px;">
                    {{ $locale === 'ar' ? 'مندوب المبيعات' : 'Sales Person' }}
                </th>
                <th>{{ $locale === 'ar' ? 'الموظف' : 'Employee' }}</th>
                <th>{{ $locale === 'ar' ? 'مجموعة الأصناف' : 'Item Group' }}</th>
                <th>{{ $locale === 'ar' ? 'الهدف الكلي' : 'Target Amount' }}</th>
                <th>{{ $locale === 'ar' ? 'نسبة التوزيع' : 'Distribution %' }}</th>
                <th>{{ $locale === 'ar' ? 'هدف الفترة' : 'Period Target' }}</th>
                <th>{{ $locale === 'ar' ? 'المبيعات الفعلية' : 'Actual Sales' }}</th>
                <th>{{ $locale === 'ar' ? 'نسبة الإنجاز' : 'Achievement %' }}</th>
                <th>{{ $locale === 'ar' ? 'نسبة العمولة' : 'Commission Rate' }}</th>
                <th>{{ $locale === 'ar' ? 'قيمة العمولة' : 'Commission Amount' }}</th>
                <th>{{ $locale === 'ar' ? 'عدد الفواتير' : 'Invoice Count' }}</th>
            </tr>
        </thead>

        <tbody>
        @forelse($rows as $index => $row)
            @php
                $achievement = $row['achievement_percentage'];
                $badgeClass  = $achievement >= 80 ? 'badge-high' : 'badge-mid';
            @endphp
            <tr>
                <td class="td-index">{{ $index + 1 }}</td>

                <td class="td-name">
                    {{ $locale === 'ar'
                        ? ($row['sales_person_name_ar'] ?? $row['sales_person_name_en'])
                        : ($row['sales_person_name_en'] ?? $row['sales_person_name_ar'])
                    }}
                </td>

                <td>{{ $row['employee_id'] }}</td>

                <td>
                    {{ $locale === 'ar'
                        ? ($row['item_group_name_ar'] ?? $row['item_group_name_en'])
                        : ($row['item_group_name_en'] ?? $row['item_group_name_ar'])
                    }}
                </td>

                <td>{{ number_format($row['target_amount'], 2) }}</td>
                <td>{{ number_format($row['total_distribution_percentage'], 2) }}%</td>
                <td>{{ number_format($row['period_target'], 2) }}</td>
                <td>{{ number_format($row['actual_sales'], 2) }}</td>

                <td>
                    <span class="badge {{ $badgeClass }}">
                        {{ number_format($achievement, 2) }}%
                    </span>
                </td>

                <td>{{ number_format($row['commission_rate'], 2) }}%</td>
                <td>{{ number_format($row['commission_amount'], 2) }}</td>
                <td>{{ $row['invoice_count'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="no-data">
                    {{ $locale === 'ar' ? 'لا توجد بيانات' : 'No data available' }}
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>