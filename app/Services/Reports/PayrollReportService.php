<?php

namespace App\Services\Reports;

use App\Models\Company;
use App\Models\Employee;
use App\Models\CompanyAccountSetting;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayrollReportService
{
    public function report(int $companyId, array $filters): array
    {
        $month = $filters['month'];
        $year = $filters['year'];

        $fromDate = Carbon::create($year, $month, 1)->startOfMonth();
        $toDate = Carbon::create($year, $month, 1)->endOfMonth();

        $employees = Employee::query()
            ->with([
                'department',
                'activeSalary',
                'allowances',
                'leaveBalances',
            ])
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        $rows = $employees->map(function ($employee) use ($fromDate, $toDate) {
            $salary = $employee->activeSalary;

            $basicSalary = (float) ($salary?->salary_value ?? 0);

            $allowancesTotal = (float) $employee->allowances->sum('amount');

            $leaveDeduction = (float) $employee->leaves()
                ->whereBetween('from_date', [$fromDate->toDateString(), $toDate->toDateString()])
                ->where('deduct_from_salary', true)
                ->sum('salary_deduction_amount');

            $socialSecurityDeduction = (float) ($salary?->social_security_deduction ?? 0);
            $insuranceDeduction = (float) ($salary?->insurance_deduction ?? 0);
            $taxDeduction = (float) ($salary?->tax_deduction ?? 0);

            $totalDeductions =
                $socialSecurityDeduction +
                $insuranceDeduction +
                $taxDeduction +
                $leaveDeduction;

            $netSalary = $basicSalary + $allowancesTotal - $totalDeductions;

            return [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name_en ?? $employee->full_name_ar,
                'job_title' => $employee->job_title,
                'department' => $employee->department?->name_en ?? $employee->department?->name_ar,

                'salary_value' => round($basicSalary, 2),
                'salary_mode' => $salary?->salary_mode,

                'bank_account_name' => $salary?->bank_account_name,
                'bank_account_number' => $salary?->bank_account_number,
                'iban' => $salary?->iban,

                'social_security_deduction' => round($socialSecurityDeduction, 2),
                'insurance_deduction' => round($insuranceDeduction, 2),
                'tax_deduction' => round($taxDeduction, 2),
                'allowances_total' => round($allowancesTotal, 2),
                'leave_deduction' => round($leaveDeduction, 2),
                'total_deductions' => round($totalDeductions, 2),
                'net_salary' => round($netSalary, 2),
            ];
        });

        return [
            'period' => [
                'month' => (int) $month,
                'year' => (int) $year,
                'from_date' => $fromDate->toDateString(),
                'to_date' => $toDate->toDateString(),
            ],
            'summary' => [
                'employees_count' => $rows->count(),
                'total_salary' => round($rows->sum('salary_value'), 2),
                'total_allowances' => round($rows->sum('allowances_total'), 2),
                'total_deductions' => round($rows->sum('total_deductions'), 2),
                'total_net_salary' => round($rows->sum('net_salary'), 2),
            ],
            'rows' => $rows->values(),
        ];
    }

    public function postJournalEntries(int $companyId, array $filters): array
{
    return DB::transaction(function () use ($companyId, $filters) {
        $report = $this->report($companyId, $filters);

        $month = $report['period']['month'];
        $year = $report['period']['year'];

        $alreadyPosted = JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('source_type', 'payroll')
            ->where('description', 'like', "%Monthly Payroll%{$month}-{$year}%")
            ->exists();

        if ($alreadyPosted) {
            throw new RuntimeException("Payroll entries already posted for {$month}/{$year}.");
        }

        $settings = CompanyAccountSetting::query()
            ->where('company_id', $companyId)
            ->first();

        if (
            ! $settings ||
            ! $settings->default_direct_expense_account_id ||
            ! $settings->default_bank_account_id ||
            ! $settings->default_cash_account_id
        ) {
            throw new RuntimeException(
                'Salary expense, bank account, and cash account must be configured in default accounts.'
            );
        }

        $createdEntries = [];
        $postedEmployees = [];

        foreach ($report['rows'] as $row) {
            $netSalary = (float) $row['net_salary'];

            if ($netSalary <= 0) {
                continue;
            }

            $paymentAccountId = match ($row['salary_mode']) {
                'bank_transfer' => $settings->default_bank_account_id,
                'cash' => $settings->default_cash_account_id,
                default => throw new RuntimeException(
                    'Invalid salary payment mode for employee: ' . $row['employee_name']
                ),
            };

            $entryNumber = $this->generateEntryNumber($companyId);

            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $entryNumber,
                'entry_date' => now()->toDateString(),
                'total_debit' => $netSalary,
                'total_credit' => $netSalary,
                'description' => 'Monthly Payroll | ' . $row['employee_name'] . ' | ' . $month . '-' . $year,
                'source_type' => 'payroll',
                'status' => 'posted',
                'posted_at' => now(),
                'created_by' => auth('api')->id(),
            ]);

            $journalEntry->lines()->create([
                'company_id' => $companyId,
                'account_id' => $settings->default_direct_expense_account_id,
                'debit' => $netSalary,
                'credit' => 0,
                'note' => 'Payroll Expense - ' . $row['employee_name'],
            ]);

            $journalEntry->lines()->create([
                'company_id' => $companyId,
                'account_id' => $paymentAccountId,
                'debit' => 0,
                'credit' => $netSalary,
                'note' => 'Payroll Payment - ' . $row['employee_name'],
            ]);

            $createdEntries[] = $journalEntry->id;

            $postedEmployees[] = [
                'employee_id' => $row['employee_id'],
                'employee_name' => $row['employee_name'],
                'net_salary' => round($netSalary, 2),
                'salary_mode' => $row['salary_mode'],
                'journal_entry_id' => $journalEntry->id,
                'journal_entry_number' => $journalEntry->entry_number,
            ];
        }

        return [
            'period' => $report['period'],

            'summary' => [
                'employees_count' => count($postedEmployees),
                'total_net_salary' => round(
                    collect($postedEmployees)->sum('net_salary'),
                    2
                ),
                'created_journal_entries_count' => count($createdEntries),
            ],

            'employees' => $postedEmployees,

            'journal_entry_ids' => $createdEntries,
        ];
    });
}
public function reportByDateRange(int $companyId, array $filters): array
{
    $fromDate = Carbon::parse($filters['from_date'])->startOfDay();
    $toDate = Carbon::parse($filters['to_date'])->endOfDay();

    $employees = Employee::query()
        ->with([
            'department',
            'activeSalary',
            'allowances',
            'leaves',
        ])
        ->where('company_id', $companyId)
        ->where('status', 'active')
        ->get();

    $rows = $employees->map(function ($employee) use ($fromDate, $toDate) {
        $salary = $employee->activeSalary;

        $basicSalary = (float) ($salary?->salary_value ?? 0);
        $allowancesTotal = (float) $employee->allowances->sum('amount');

        $leaveDeduction = (float) $employee->leaves()
            ->whereBetween('from_date', [
                $fromDate->toDateString(),
                $toDate->toDateString(),
            ])
            ->where('deduct_from_salary', true)
            ->sum('salary_deduction_amount');

        $socialSecurityDeduction = (float) ($salary?->social_security_deduction ?? 0);
        $insuranceDeduction = (float) ($salary?->insurance_deduction ?? 0);
        $taxDeduction = (float) ($salary?->tax_deduction ?? 0);

        $totalDeductions =
            $socialSecurityDeduction +
            $insuranceDeduction +
            $taxDeduction +
            $leaveDeduction;

        $netSalary = $basicSalary + $allowancesTotal - $totalDeductions;

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name_en ?? $employee->full_name_ar,
            'job_title' => $employee->job_title,
            'department' => $employee->department?->name_en ?? $employee->department?->name_ar,

            'salary_value' => round($basicSalary, 2),
            'salary_mode' => $salary?->salary_mode,

            'bank_account_name' => $salary?->bank_account_name,
            'bank_account_number' => $salary?->bank_account_number,
            'iban' => $salary?->iban,

            'social_security_deduction' => round($socialSecurityDeduction, 2),
            'insurance_deduction' => round($insuranceDeduction, 2),
            'tax_deduction' => round($taxDeduction, 2),
            'allowances_total' => round($allowancesTotal, 2),
            'leave_deduction' => round($leaveDeduction, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_salary' => round($netSalary, 2),
        ];
    });

    return [
        'period' => [
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
        ],
        'summary' => [
            'employees_count' => $rows->count(),
            'total_salary' => round($rows->sum('salary_value'), 2),
            'total_allowances' => round($rows->sum('allowances_total'), 2),
            'total_deductions' => round($rows->sum('total_deductions'), 2),
            'total_net_salary' => round($rows->sum('net_salary'), 2),
        ],
        'rows' => $rows->values(),
    ];
}

    private function generateEntryNumber(int $companyId): string
    {
        $year = now()->format('Y');

        $lastEntryNumber = JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('entry_number', 'like', "JV-{$year}-%")
            ->orderByDesc('id')
            ->value('entry_number');

        $nextNumber = 1;

        if ($lastEntryNumber) {
            $parts = explode('-', $lastEntryNumber);
            $nextNumber = ((int) end($parts)) + 1;
        }

        return sprintf('JV-%s-%05d', $year, $nextNumber);
    }
}