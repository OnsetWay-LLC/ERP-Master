<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ app()->getLocale() === 'ar' ? 'قائمة الدخل' : 'Profit & Loss Statement' }}</title>

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

        .section-title {
            font-size: 12px;
            font-weight: bold;
            padding: 7px;
            margin-top: 10px;
            border: 1px solid #d1d5db;
            background: #f3f4f6;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .report-table th,
        .report-table td {
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

        .income-color {
            color: #0f766e;
        }

        .expense-color {
            color: #1d4ed8;
        }

        .profit {
            color: #0f766e;
            font-weight: bold;
        }

        .loss {
            color: #b91c1c;
            font-weight: bold;
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
        {{ app()->getLocale() === 'ar' ? 'قائمة الدخل' : 'Profit & Loss Statement' }}
    </div>

    <div class="report-period">
        @if(!empty($report['financial_year']))
            {{ app()->getLocale() === 'ar' ? 'السنة المالية:' : 'Financial Year:' }}
            {{ $report['financial_year'] }}
        @endif
    </div>
</div>

<div class="section-title income-color">
    {{ app()->getLocale() === 'ar' ? 'الإيرادات' : 'Income' }}
</div>

<table class="report-table">
    <thead>
    <tr>
        <th>#</th>
        <th>{{ app()->getLocale() === 'ar' ? 'رقم الحساب' : 'Account No.' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب عربي' : 'Account Name AR' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب إنجليزي' : 'Account Name EN' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'القيمة' : 'Value' }}</th>
    </tr>
    </thead>

    <tbody>
    @forelse($report['income']['rows'] ?? [] as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['account_number'] ?? '—' }}</td>
            <td class="text-right">{{ $row['account_name_ar'] ?? '—' }}</td>
            <td class="text-left">{{ $row['account_name_en'] ?? '—' }}</td>
            <td class="amount income-color">{{ number_format($row['value'] ?? 0, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="empty-message">
                {{ app()->getLocale() === 'ar' ? 'لا توجد إيرادات.' : 'No income data.' }}
            </td>
        </tr>
    @endforelse
    </tbody>

    <tfoot>
    <tr class="total-row">
        <td colspan="4">{{ app()->getLocale() === 'ar' ? 'إجمالي الإيرادات' : 'Total Income' }}</td>
        <td class="amount income-color">{{ number_format($report['income']['total_income'] ?? 0, 2) }}</td>
    </tr>
    </tfoot>
</table>

<div class="section-title expense-color">
    {{ app()->getLocale() === 'ar' ? 'المصاريف' : 'Expenses' }}
</div>

<table class="report-table">
    <thead>
    <tr>
        <th>#</th>
        <th>{{ app()->getLocale() === 'ar' ? 'رقم الحساب' : 'Account No.' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب عربي' : 'Account Name AR' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'اسم الحساب إنجليزي' : 'Account Name EN' }}</th>
        <th>{{ app()->getLocale() === 'ar' ? 'القيمة' : 'Value' }}</th>
    </tr>
    </thead>

    <tbody>
    @forelse($report['expenses']['rows'] ?? [] as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['account_number'] ?? '—' }}</td>
            <td class="text-right">{{ $row['account_name_ar'] ?? '—' }}</td>
            <td class="text-left">{{ $row['account_name_en'] ?? '—' }}</td>
            <td class="amount expense-color">{{ number_format($row['value'] ?? 0, 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="empty-message">
                {{ app()->getLocale() === 'ar' ? 'لا توجد مصاريف.' : 'No expenses data.' }}
            </td>
        </tr>
    @endforelse
    </tbody>

    <tfoot>
    <tr class="total-row">
        <td colspan="4">{{ app()->getLocale() === 'ar' ? 'إجمالي المصاريف' : 'Total Expenses' }}</td>
        <td class="amount expense-color">{{ number_format($report['expenses']['total_expenses'] ?? 0, 2) }}</td>
    </tr>
    </tfoot>
</table>

@php
    $isProfit = ($report['result_type'] ?? 'profit') === 'profit';
@endphp

<table class="summary-table">
    <tr>
        <td class="summary-label">{{ app()->getLocale() === 'ar' ? 'إجمالي الإيرادات' : 'Total Income' }}</td>
        <td class="summary-label">{{ app()->getLocale() === 'ar' ? 'إجمالي المصاريف' : 'Total Expenses' }}</td>
        <td class="summary-label">{{ app()->getLocale() === 'ar' ? 'صافي النتيجة' : 'Net Result' }}</td>
        <td class="summary-label">{{ app()->getLocale() === 'ar' ? 'نوع النتيجة' : 'Result Type' }}</td>
    </tr>

    <tr>
        <td class="summary-value income-color">{{ number_format($report['income']['total_income'] ?? 0, 2) }}</td>
        <td class="summary-value expense-color">{{ number_format($report['expenses']['total_expenses'] ?? 0, 2) }}</td>
        <td class="summary-value {{ $isProfit ? 'profit' : 'loss' }}">
            {{ number_format(abs($report['net_result'] ?? 0), 2) }}
        </td>
        <td class="summary-value {{ $isProfit ? 'profit' : 'loss' }}">
            {{ $isProfit
                ? (app()->getLocale() === 'ar' ? 'ربح' : 'Profit')
                : (app()->getLocale() === 'ar' ? 'خسارة' : 'Loss')
            }}
        </td>
    </tr>
</table>

</body>
</html>