<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ app()->getLocale() === 'ar' ? 'ميزان المراجعة' : 'Trial Balance' }}</title>

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

        .company-logo {
            margin-bottom: 5px;
        }

        .company-logo img {
            max-height: 60px;
            max-width: 150px;
        }

        .company-name {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .company-info {
            font-size: 9px;
            color: #4b5563;
            line-height: 1.5;
        }

        .custom-report-header {
            font-size: 9px;
            color: #374151;
            margin-top: 5px;
            white-space: pre-line;
        }

        .report-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .report-period {
            font-size: 10px;
            color: #4b5563;
        }

        .status-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .status-box td {
            border: 1px solid #d1d5db;
            padding: 7px;
            text-align: center;
        }

        .status-label {
            background: #f3f4f6;
            font-weight: bold;
        }

        .balanced {
            color: #0f766e;
            font-weight: bold;
        }

        .unbalanced {
            color: #b91c1c;
            font-weight: bold;
        }

        table.trial-table {
            width: 100%;
            border-collapse: collapse;
        }

        .trial-table th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .trial-table th,
        .trial-table td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .total-row {
            background: #f9fafb;
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .summary-table td {
            border: 1px solid #d1d5db;
            padding: 7px;
            text-align: center;
        }

        .summary-label {
            background: #f3f4f6;
            font-weight: bold;
        }

        .summary-value {
            font-weight: bold;
            font-size: 11px;
        }

        .empty-message {
            text-align: center;
            padding: 15px;
            color: #6b7280;
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
        {{ app()->getLocale() === 'ar'
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

<div class="report-header">
    <div class="report-title">
        {{ app()->getLocale() === 'ar' ? 'ميزان المراجعة' : 'Trial Balance' }}
    </div>

    <div class="report-period">
        {{ app()->getLocale() === 'ar' ? 'الفترة:' : 'Period:' }}

        {{ $report['from_date'] ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All') }}

        -

        {{ $report['to_date'] ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All') }}
    </div>
</div>

<table class="status-box">
    <tr>
        <td class="status-label">
            {{ app()->getLocale() === 'ar' ? 'حالة الميزان' : 'Trial Balance Status' }}
        </td>
        <td>
            @if($report['is_balanced'] ?? true)
                <span class="balanced">
                    {{ app()->getLocale() === 'ar' ? 'متوازن' : 'Balanced' }}
                </span>
            @else
                <span class="unbalanced">
                    {{ app()->getLocale() === 'ar' ? 'غير متوازن' : 'Not Balanced' }}
                </span>
            @endif
        </td>
    </tr>
</table>

<table class="trial-table">
    <thead>
    <tr>
        <th>#</th>
        <th>{{ app()->getLocale() === 'ar' ? 'رقم الحساب' : 'Account No' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب عربي' : 'Account Name AR' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب إنجليزي' : 'Account Name EN' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'مدين' : 'Debit' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'دائن' : 'Credit' }}</th>
    </tr>
    </thead>

    <tbody>
    @forelse($report['rows'] ?? [] as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['account_number'] ?? '—' }}</td>
            <td class="text-right">{{ $row['account_name_ar'] ?? '—' }}</td>
            <td class="text-left">{{ $row['account_name_en'] ?? '—' }}</td>
            <td class="amount">
                {{ ($row['debit_balance'] ?? 0) ? number_format($row['debit_balance'], 2) : '—' }}
            </td>
            <td class="amount">
                {{ ($row['credit_balance'] ?? 0) ? number_format($row['credit_balance'], 2) : '—' }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="empty-message">
                {{ app()->getLocale() === 'ar' ? 'لا توجد بيانات.' : 'No data found.' }}
            </td>
        </tr>
    @endforelse
    </tbody>

    <tfoot>
    <tr class="total-row">
        <td colspan="4">
            {{ app()->getLocale() === 'ar' ? 'المجموع الكلي' : 'Grand Total' }}
        </td>
        <td class="amount">
            {{ number_format($report['totals']['total_debit'] ?? 0, 2) }}
        </td>
        <td class="amount">
            {{ number_format($report['totals']['total_credit'] ?? 0, 2) }}
        </td>
    </tr>
    </tfoot>
</table>

<table class="summary-table">
    <tr>
        <td class="summary-label">
            {{ app()->getLocale() === 'ar' ? 'إجمالي المدين' : 'Total Debit' }}
        </td>
        <td class="summary-label">
            {{ app()->getLocale() === 'ar' ? 'إجمالي الدائن' : 'Total Credit' }}
        </td>
        <td class="summary-label">
            {{ app()->getLocale() === 'ar' ? 'الفرق' : 'Difference' }}
        </td>
    </tr>

    <tr>
        <td class="summary-value">
            {{ number_format($report['totals']['total_debit'] ?? 0, 2) }}
        </td>
        <td class="summary-value">
            {{ number_format($report['totals']['total_credit'] ?? 0, 2) }}
        </td>
        <td class="summary-value {{ ($report['is_balanced'] ?? true) ? 'balanced' : 'unbalanced' }}">
            {{ number_format($report['totals']['difference'] ?? 0, 2) }}
        </td>
    </tr>
</table>

</body>
</html>