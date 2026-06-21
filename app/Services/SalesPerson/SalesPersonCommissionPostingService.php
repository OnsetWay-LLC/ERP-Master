<?php

namespace App\Services\SalesPerson;

use App\Models\CompanyAccountSetting;
use App\Models\EmployeeAllowance;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SalesPersonCommission;
use App\Models\SalesPersonTarget;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesPersonCommissionPostingService
{
    public function post(array $data, int $companyId, int $userId): SalesPersonCommission
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            $target = SalesPersonTarget::query()
                ->with([
                    'salesPerson.employee',
                    'salesPerson.commissionAccount',
                    'salesPerson.payrollAccount',
                    'itemGroup',
                    'monthlyDistribution.lines',
                ])
                ->where('id', $data['sales_person_target_id'])
                ->firstOrFail();

            $salesPerson = $target->salesPerson;

            if ((int) $salesPerson->company_id !== (int) $companyId) {
                throw new RuntimeException('Sales person does not belong to this company.');
            }

            [$months, $periodStartDate, $periodEndDate, $totalPeriodTarget] =
                $this->resolvePeriodFromDistribution($target, (int) $data['fiscal_year']);
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $companyId,
        $periodEndDate,
        'create'
    );
            $alreadyPosted = SalesPersonCommission::query()
                ->where('sales_person_id', $salesPerson->id)
                ->where('sales_person_target_id', $target->id)
                ->where('from_date', $periodStartDate)
                ->where('to_date', $periodEndDate)
                ->where('status', 'posted')
                ->exists();

            if ($alreadyPosted) {
                throw new RuntimeException('Commission already posted for this period.');
            }

            $totalActualSales = $this->calculateActualSalesForDistributionMonths(
                companyId: $companyId,
                salesPersonId: $salesPerson->id,
                itemGroupId: $target->item_group_id,
                fiscalYear: (int) $data['fiscal_year'],
                months: $months
            );

            $achievementPercentage = $totalPeriodTarget > 0
                ? round(($totalActualSales / $totalPeriodTarget) * 100, 2)
                : 0;

            if ($totalActualSales < $totalPeriodTarget) {
                throw new RuntimeException('Sales person did not achieve the target.');
            }

            $commissionAmount = round(
                ($totalActualSales * (float) $salesPerson->commission_rate) / 100,
                2
            );

            if ($commissionAmount <= 0) {
                throw new RuntimeException('Commission amount must be greater than zero.');
            }

            [$commissionAccountId, $payrollAccountId] = $this->resolveAccounts($salesPerson, $companyId);

            $journalEntry = $this->createPostedJournalEntry(
                companyId: $companyId,
                userId: $userId,
                entryDate: $periodEndDate,
                commissionAmount: $commissionAmount,
                commissionAccountId: $commissionAccountId,
                payrollAccountId: $payrollAccountId,
                salesPersonName: $salesPerson->sales_person_name_en
            );

            $employeeAllowance = EmployeeAllowance::query()->create([
                'employee_id' => $salesPerson->employee_id,
                'name_ar' => 'بدل عمولة',
                'name_en' => 'Commission Allowance',
                'amount' => $commissionAmount,
            ]);

            return SalesPersonCommission::query()->create([
                'company_id' => $companyId,
                'sales_person_id' => $salesPerson->id,
                'sales_person_target_id' => $target->id,
                'from_date' => $periodStartDate,
                'to_date' => $periodEndDate,
                'target_amount' => $target->target_amount,
                'total_period_target' => $totalPeriodTarget,
                'total_actual_sales' => $totalActualSales,
                'achievement_percentage' => $achievementPercentage,
                'commission_rate' => $salesPerson->commission_rate,
                'commission_amount' => $commissionAmount,
                'journal_entry_id' => $journalEntry->id,
                'employee_allowance_id' => $employeeAllowance->id,
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => $userId,
            ]);
        });
    }

    private function resolvePeriodFromDistribution(SalesPersonTarget $target, int $fiscalYear): array
    {
        $lines = $target->monthlyDistribution->lines
            ->where('percentage', '>', 0)
            ->sortBy('month')
            ->values();

        if ($lines->isEmpty()) {
            throw new RuntimeException('Monthly distribution has no active months.');
        }

        $months = $lines->pluck('month')
            ->map(fn ($month) => (int) $month)
            ->values()
            ->all();

        $startMonth = min($months);
        $endMonth = max($months);

        $periodStartDate = now()
            ->setDate($fiscalYear, $startMonth, 1)
            ->startOfMonth()
            ->toDateString();

        $periodEndDate = now()
            ->setDate($fiscalYear, $endMonth, 1)
            ->endOfMonth()
            ->toDateString();

        if (now()->toDateString() <= $periodEndDate) {
            throw new RuntimeException('Cannot post commission before distribution period ends.');
        }

        $totalPercentage = $lines->sum('percentage');

        $totalPeriodTarget = round(
            ((float) $target->target_amount * (float) $totalPercentage) / 100,
            2
        );

        return [$months, $periodStartDate, $periodEndDate, $totalPeriodTarget];
    }

    private function calculateActualSalesForDistributionMonths(
        int $companyId,
        int $salesPersonId,
        int $itemGroupId,
        int $fiscalYear,
        array $months
    ): float {
        $total = DB::table('sales_invoice_items as sii')
            ->join('sales_invoices as si', 'si.id', '=', 'sii.sales_invoice_id')
            ->join('items as i', 'i.id', '=', 'sii.item_id')
            ->where('si.company_id', $companyId)
            ->where('si.sales_person_id', $salesPersonId)
            ->where('si.status', 'submitted')
            ->whereNull('si.deleted_at')
            ->whereYear('si.posting_date', $fiscalYear)
            ->whereIn(DB::raw('MONTH(si.posting_date)'), $months)
            ->where('i.item_group_id', $itemGroupId)
            ->sum('sii.amount');

        return round((float) $total, 2);
    }

    private function resolveAccounts($salesPerson, int $companyId): array
    {
        if ($salesPerson->use_default_account) {
            $settings = CompanyAccountSetting::query()
                ->where('company_id', $companyId)
                ->first();

            if (
                !$settings ||
                !$settings->default_direct_expense_account_id ||
                !$settings->default_payable_account_id
            ) {
                throw new RuntimeException('Default commission posting accounts are not configured.');
            }

            return [
                $settings->default_direct_expense_account_id,
                $settings->default_payable_account_id,
            ];
        }

        if (!$salesPerson->commission_account_id || !$salesPerson->payroll_account_id) {
            throw new RuntimeException('Manual commission posting accounts are not configured.');
        }

        return [
            $salesPerson->commission_account_id,
            $salesPerson->payroll_account_id,
        ];
    }

    private function createPostedJournalEntry(
        int $companyId,
        int $userId,
        string $entryDate,
        float $commissionAmount,
        int $commissionAccountId,
        int $payrollAccountId,
        string $salesPersonName
    ): JournalEntry {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $companyId,
        $entryDate,
        'create'
    );
        $entry = JournalEntry::query()->create([
            'company_id' => $companyId,
            'entry_number' => $this->generateEntryNumber($companyId),
            'entry_date' => $entryDate,
            'description' => 'Sales person commission posting - ' . $salesPersonName,
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => $userId,
            'total_debit' => $commissionAmount,
            'total_credit' => $commissionAmount,
        ]);

        $debitLine = JournalEntryLine::query()->create([
            'company_id' => $companyId,
            'journal_entry_id' => $entry->id,
            'account_id' => $commissionAccountId,
            'debit' => $commissionAmount,
            'credit' => 0,
            'note' => 'Sales commission expense',
        ]);

        $creditLine = JournalEntryLine::query()->create([
            'company_id' => $companyId,
            'journal_entry_id' => $entry->id,
            'account_id' => $payrollAccountId,
            'debit' => 0,
            'credit' => $commissionAmount,
            'note' => 'Sales commission payable',
        ]);

        $this->createLedgerLine($companyId, $entry, $debitLine, $userId);
        $this->createLedgerLine($companyId, $entry, $creditLine, $userId);

        return $entry->fresh(['lines.account']);
    }

    private function createLedgerLine(
        int $companyId,
        JournalEntry $entry,
        JournalEntryLine $line,
        int $userId
    ): void {
        $lastBalance = GeneralLedger::query()
            ->where('company_id', $companyId)
            ->where('account_id', $line->account_id)
            ->latest('id')
            ->value('balance') ?? 0;

        $newBalance = ((float) $lastBalance + (float) $line->debit) - (float) $line->credit;

        GeneralLedger::query()->create([
            'company_id' => $companyId,
            'journal_entry_id' => $entry->id,
            'journal_entry_line_id' => $line->id,
            'account_id' => $line->account_id,
            'entry_date' => $entry->entry_date,
            'debit' => $line->debit,
            'credit' => $line->credit,
            'balance' => $newBalance,
            'description' => $entry->description,
            'created_by' => $userId,
        ]);
    }

    private function generateEntryNumber(int $companyId): string
    {
        $lastId = JournalEntry::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'JV-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
}