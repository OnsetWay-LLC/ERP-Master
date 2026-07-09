<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>Payroll Report</title>
    <style>
        body {
            font-family: dejavusans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        .company-header {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .company-logo {
            text-align: center;
            margin-bottom: 6px;
        }

        .company-logo img {
            max-height: 65px;
            max-width: 160px;
        }

        .custom-header {
            text-align: center;
            font-size: 11px;
            line-height: 1.6;
            white-space: pre-line;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 12px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .summary {
            margin-bottom: 12px;
        }

        .summary td {
            font-weight: bold;
        }

        .total-row {
            background: #f9fafb;
            font-weight: bold;
        }
.company-logo {
    text-align: center;
    margin-bottom: 6px;
}

.company-logo img {
    max-height: 65px;
    max-width: 160px;
}
       
    </style>
</head>
<body dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<div class="company-header">
    

   
</div>
@if($company->logo)
    <div class="company-logo">
        <img
            src="{{ public_path($company->logo) }}"
            alt="Company Logo"
        >
    </div>
@endif
<div class="header">
    
    <div class="title">Payroll Report</div>
    <div class="subtitle">
        {{ $company->name_en ?? $company->name_ar ?? 'Company' }}
    </div>
  
    <div class="subtitle">
        Period: {{ $report['period']['from_date'] }} to {{ $report['period']['to_date'] }}
    </div>
      @if($company->report_header)
        <div class="custom-report-header">
            {!! nl2br(e($company->report_header)) !!}
        </div>
    @endif
</div>

<table class="summary">
    <tr>
        <td>Employees Count</td>
        <td>Total Salary</td>
        <td>Total Allowances</td>
        <td>Total Deductions</td>
        <td>Total Net Salary</td>
    </tr>
    <tr>
        <td>{{ $report['summary']['employees_count'] }}</td>
        <td>{{ number_format($report['summary']['total_salary'], 2) }}</td>
        <td>{{ number_format($report['summary']['total_allowances'], 2) }}</td>
        <td>{{ number_format($report['summary']['total_deductions'], 2) }}</td>
        <td>{{ number_format($report['summary']['total_net_salary'], 2) }}</td>
    </tr>
</table>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Employee</th>
        <th>Job Title</th>
        <th>Department</th>
        <th>Salary</th>
        <th>Payment</th>
        <th>Bank Account</th>
        <th>IBAN</th>
        <th>Social Security</th>
        <th>Insurance</th>
        <th>Tax</th>
        <th>Allowances</th>
        <th>Leave Deduction</th>
        <th>Net Salary</th>
    </tr>
    </thead>

    <tbody>
    @foreach($report['rows'] as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td class="text-left">{{ $row['employee_name'] }}</td>
            <td>{{ $row['job_title'] }}</td>
            <td>{{ $row['department'] }}</td>
            <td>{{ number_format($row['salary_value'], 2) }}</td>
            <td>{{ $row['salary_mode'] }}</td>
            <td>{{ $row['bank_account_number'] }}</td>
            <td>{{ $row['iban'] }}</td>
            <td>{{ number_format($row['social_security_deduction'], 2) }}</td>
            <td>{{ number_format($row['insurance_deduction'], 2) }}</td>
            <td>{{ number_format($row['tax_deduction'], 2) }}</td>
            <td>{{ number_format($row['allowances_total'], 2) }}</td>
            <td>{{ number_format($row['leave_deduction'], 2) }}</td>
            <td>{{ number_format($row['net_salary'], 2) }}</td>
        </tr>
    @endforeach
    </tbody>

    <tfoot>
    <tr class="total-row">
        <td colspan="4">Total</td>
        <td>{{ number_format($report['summary']['total_salary'], 2) }}</td>
        <td colspan="6"></td>
        <td>{{ number_format($report['summary']['total_allowances'], 2) }}</td>
        <td></td>
        <td>{{ number_format($report['summary']['total_net_salary'], 2) }}</td>
    </tr>
    </tfoot>
</table>



</body>
</html>