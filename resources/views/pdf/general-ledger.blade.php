<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">

    <title>
        {{ app()->getLocale() === 'ar' ? 'دفتر الأستاذ العام' : 'General Ledger Report' }}
    </title>

    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        /* =========================
           Company Header
        ========================== */

        .company-header {
            text-align: center;
            margin-bottom: 12px;
        }

        .company-logo {
            text-align: center;
            margin-bottom: 5px;
        }

        .company-logo img {
            max-height: 60px;
            max-width: 150px;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
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
            padding-bottom: 6px;
            border-bottom: 1px solid #d1d5db;
        }

        /* =========================
           Report Title
        ========================== */

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

        /* =========================
           Account Information
        ========================== */

        .account-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .account-info td {
            border: 1px solid #d1d5db;
            padding: 6px;
        }

        .info-label {
            font-weight: bold;
            background: #f3f4f6;
            width: 15%;
        }

        .info-value {
            width: 35%;
        }

        /* =========================
           Main Table
        ========================== */

        table.ledger-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ledger-table th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .ledger-table th,
        .ledger-table td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        .ledger-table .text-left {
            text-align: left;
        }

        .ledger-table .amount {
            text-align: right;
            white-space: nowrap;
        }

        .total-row {
            background: #f9fafb;
            font-weight: bold;
        }

        /* =========================
           Summary
        ========================== */

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

{{-- =====================================
     Company Header
===================================== --}}

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
            |
            {{ $company->phone }}
        @endif

        @if($company->country ?? null)
            |
            {{ $company->country }}
        @endif

    </div>

    @if($company->report_header)
        <div class="custom-report-header">
            {!! nl2br(e($company->report_header)) !!}
        </div>
    @endif

</div>


{{-- =====================================
     Report Title
===================================== --}}

<div class="report-header">

    <div class="report-title">
        {{ app()->getLocale() === 'ar'
            ? 'دفتر الأستاذ العام'
            : 'General Ledger Report'
        }}
    </div>

    <div class="report-period">

        {{ app()->getLocale() === 'ar' ? 'الفترة:' : 'Period:' }}

        {{ $report['filters']['from_date']
            ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All')
        }}

        -

        {{ $report['filters']['to_date']
            ?? (app()->getLocale() === 'ar' ? 'الكل' : 'All')
        }}

    </div>

</div>


{{-- =====================================
     Account Information
===================================== --}}

<table class="account-info">

    <tr>

        <td class="info-label">
            {{ app()->getLocale() === 'ar'
                ? 'رقم الحساب'
                : 'Account Number'
            }}
        </td>

        <td class="info-value">
            {{ $report['account']['account_number'] }}
        </td>

        <td class="info-label">
            {{ app()->getLocale() === 'ar'
                ? 'اسم الحساب'
                : 'Account Name'
            }}
        </td>

        <td class="info-value">
            {{ app()->getLocale() === 'ar'
                ? $report['account']['name_ar']
                : $report['account']['name_en']
            }}
        </td>

    </tr>

    <tr>

        <td class="info-label">
            {{ app()->getLocale() === 'ar'
                ? 'نوع الحساب'
                : 'Account Type'
            }}
        </td>

        <td class="info-value">
            {{ $report['account']['account_type'] ?? '—' }}
        </td>

        <td class="info-label">
            {{ app()->getLocale() === 'ar'
                ? 'الرصيد الافتتاحي'
                : 'Opening Balance'
            }}
        </td>

        <td class="info-value">
            {{ $report['opening_balance']['display'] }}
        </td>

    </tr>

</table>


{{-- =====================================
     General Ledger Table
===================================== --}}

<table class="ledger-table">

    <thead>

    <tr>

        <th>#</th>

        <th>
            {{ app()->getLocale() === 'ar'
                ? 'التاريخ'
                : 'Date'
            }}
        </th>

        <th>
            {{ app()->getLocale() === 'ar'
                ? 'رقم القيد'
                : 'Voucher No.'
            }}
        </th>

        <th>
            {{ app()->getLocale() === 'ar'
                ? 'النوع'
                : 'Type'
            }}
        </th>

        <th>
            {{ app()->getLocale() === 'ar'
                ? 'الحساب'
                : 'Account'
            }}
        </th>

        <th>
            {{ app()->getLocale() === 'ar'
                ? 'البيان'
                : 'Description'
            }}
        </th>

        <th>
            {{ app()->getLocale() === 'ar'
                ? 'مدين'
                : 'Debit'
            }}
        </th>

        <th>
            {{ app()->getLocale() === 'ar'
                ? 'دائن'
                : 'Credit'
            }}
        </th>

        <th>
            {{ app()->getLocale() === 'ar'
                ? 'الرصيد'
                : 'Balance'
            }}
        </th>

    </tr>

    </thead>

    <tbody>

    @forelse($report['rows'] as $index => $row)

        <tr>

            <td>
                {{ $index + 1 }}
            </td>

            <td>
                {{ $row['date'] ?? '—' }}
            </td>

            <td>
                {{ $row['voucher_no'] ?? '—' }}
            </td>

            <td>
                {{ $row['voucher_type'] ?? '—' }}
            </td>

            <td class="text-left">

                @if($row['account']['account_number'] ?? null)
                    {{ $row['account']['account_number'] }}
                    -
                @endif

                {{ app()->getLocale() === 'ar'
                    ? ($row['account']['name_ar'] ?? '—')
                    : ($row['account']['name_en'] ?? '—')
                }}

            </td>

            <td class="text-left">
                {{ $row['description'] ?? '—' }}
            </td>

            <td class="amount">
                {{ $row['debit'] ?? '0.00' }}
            </td>

            <td class="amount">
                {{ $row['credit'] ?? '0.00' }}
            </td>

            <td class="amount">
                {{ $row['balance'] ?? '0.00' }}
            </td>

        </tr>

    @empty

        <tr>
            <td colspan="9" class="empty-message">
                {{ app()->getLocale() === 'ar'
                    ? 'لا توجد حركات خلال الفترة المحددة.'
                    : 'No ledger entries found for the selected period.'
                }}
            </td>
        </tr>

    @endforelse

    </tbody>

    <tfoot>

    <tr class="total-row">

        <td colspan="6">
            {{ app()->getLocale() === 'ar'
                ? 'المجموع الكلي'
                : 'Grand Total'
            }}
        </td>

        <td class="amount">
            {{ $report['grand_total']['total_debit'] }}
        </td>

        <td class="amount">
            {{ $report['grand_total']['total_credit'] }}
        </td>

        <td class="amount">
            {{ $report['closing_balance']['display'] }}
        </td>

    </tr>

    </tfoot>

</table>


{{-- =====================================
     Summary
===================================== --}}

<table class="summary-table">

    <tr>

        <td class="summary-label">
            {{ app()->getLocale() === 'ar'
                ? 'الرصيد الافتتاحي'
                : 'Opening Balance'
            }}
        </td>

        <td class="summary-label">
            {{ app()->getLocale() === 'ar'
                ? 'إجمالي المدين'
                : 'Total Debit'
            }}
        </td>

        <td class="summary-label">
            {{ app()->getLocale() === 'ar'
                ? 'إجمالي الدائن'
                : 'Total Credit'
            }}
        </td>

        <td class="summary-label">
            {{ app()->getLocale() === 'ar'
                ? 'الرصيد الختامي'
                : 'Closing Balance'
            }}
        </td>

    </tr>

    <tr>

        <td class="summary-value">
            {{ $report['opening_balance']['display'] }}
        </td>

        <td class="summary-value">
            {{ $report['grand_total']['total_debit'] }}
        </td>

        <td class="summary-value">
            {{ $report['grand_total']['total_credit'] }}
        </td>

        <td class="summary-value">
            {{ $report['closing_balance']['display'] }}
        </td>

    </tr>

</table>

</body>
</html>