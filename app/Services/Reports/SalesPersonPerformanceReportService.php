<?php

namespace App\Services\Reports;

use App\Models\EmployeeAllowance;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesPersonPerformanceReportService
{
    public function report(int $companyId, array $filters): array
    {
        $fromDate = $filters['from_date'];
        $toDate = $filters['to_date'];

        $targets = DB::table('sales_person_targets as spt')
            ->join('sales_people as sp', 'sp.id', '=', 'spt.sales_person_id')
            ->leftJoin('employees as e', 'e.id', '=', 'sp.employee_id')
            ->leftJoin('warehouses as w', 'w.sales_person_id', '=', 'sp.id')
            ->join('item_groups as ig', 'ig.id', '=', 'spt.item_group_id')
            ->join('monthly_distributions as md', 'md.id', '=', 'spt.monthly_distribution_id')
            ->where('sp.company_id', $companyId)
            ->where('sp.is_active', true)
            ->where('md.is_active', true)
            ->when(!empty($filters['sales_person_id']), fn ($q) =>
                $q->where('sp.id', $filters['sales_person_id'])
            )
            ->when(!empty($filters['item_group_id']), fn ($q) =>
                $q->where('ig.id', $filters['item_group_id'])
            )
            ->when(!empty($filters['warehouse_id']), fn ($q) =>
                $q->where('w.id', $filters['warehouse_id'])
            )
            ->select([
                'spt.id as target_id',
                'spt.target_amount',
                'spt.monthly_distribution_id',

                'sp.id as sales_person_id',
                'sp.sales_person_name_ar',
                'sp.sales_person_name_en',
                'sp.commission_rate',

                'e.id as employee_id',
                'e.full_name_ar as employee_name_ar',
                'e.full_name_en as employee_name_en',

                'w.id as warehouse_id',
                'w.name_ar as warehouse_name_ar',
                'w.name_en as warehouse_name_en',

                'ig.id as item_group_id',
                'ig.name_ar as item_group_name_ar',
                'ig.name_en as item_group_name_en',
            ])
            ->orderBy('sp.id')
            ->get();

        $rows = [];

        foreach ($targets as $target) {
            $lines = $this->distributionLines((int) $target->monthly_distribution_id);

            $monthlyTargets = $this->monthlyTargets(
                (float) $target->target_amount,
                $lines
            );

            $totalPeriodTarget = round(array_sum(array_column($monthlyTargets, 'monthly_target')), 2);

            $totalActualSales = $this->getTotalActualSales(
                $companyId,
                (int) $target->sales_person_id,
                (int) $target->item_group_id,
                $fromDate,
                $toDate,
                $filters['warehouse_id'] ?? null
            );

            $targetIsValid = abs($totalPeriodTarget - (float) $target->target_amount) <= 0.01;
            $periodIsFinished = $this->periodIsFinished($toDate, $lines);

            $targetAchieved = $targetIsValid
                && $periodIsFinished
                && $totalActualSales >= $totalPeriodTarget;

            $journalEntryNumber = $this->existingJournalEntryNumber($companyId, [
                'target_id' => $target->target_id,
                'sales_person_id' => $target->sales_person_id,
                'item_group_id' => $target->item_group_id,
            ], $filters);

            foreach ($monthlyTargets as $monthRow) {
                $actualSales = $this->getActualSalesByMonth(
                    $companyId,
                    (int) $target->sales_person_id,
                    (int) $target->item_group_id,
                    (int) $monthRow['month'],
                    $fromDate,
                    $toDate,
                    $filters['warehouse_id'] ?? null
                );

                $invoiceCount = $this->getInvoiceCountByMonth(
                    $companyId,
                    (int) $target->sales_person_id,
                    (int) $target->item_group_id,
                    (int) $monthRow['month'],
                    $fromDate,
                    $toDate,
                    $filters['warehouse_id'] ?? null
                );

                $commissionAmount = round(
                    (float) $monthRow['monthly_target'] * ((float) $target->commission_rate / 100),
                    2
                );

                $rows[] = [
                    'target_id' => $target->target_id,

                    'sales_person_id' => $target->sales_person_id,
                    'sales_person_name_ar' => $target->sales_person_name_ar,
                    'sales_person_name_en' => $target->sales_person_name_en,

                    'employee_id' => $target->employee_id,
                    'employee_name_ar' => $target->employee_name_ar,
                    'employee_name_en' => $target->employee_name_en,

                    'warehouse_id' => $target->warehouse_id,
                    'warehouse_name_ar' => $target->warehouse_name_ar,
                    'warehouse_name_en' => $target->warehouse_name_en,

                    'item_group_id' => $target->item_group_id,
                    'item_group_name_ar' => $target->item_group_name_ar,
                    'item_group_name_en' => $target->item_group_name_en,

                    'month' => $monthRow['month'],
                    'month_percentage' => $monthRow['percentage'],

                    'target_amount' => round((float) $target->target_amount, 2),
                    'monthly_target' => $monthRow['monthly_target'],
                    'total_period_target' => $totalPeriodTarget,

                    'actual_sales' => $actualSales,
                    'total_actual_sales' => $totalActualSales,

                    'commission_rate' => round((float) $target->commission_rate, 2),
                    'commission_amount' => $commissionAmount,

                    'invoice_count' => $invoiceCount,
                    'journal_entry_number' => $journalEntryNumber ?? '-',

                    'target_is_valid' => $targetIsValid,
                    'period_is_finished' => $periodIsFinished,
                    'target_achieved' => $targetAchieved,

                    'data_error' => !$targetIsValid
                        ? 'Total Period Target does not equal Target Amount.'
                        : null,
                ];
            }
        }

        $uniqueRows = collect($rows)->unique('target_id');

        return [
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'sales_person_id' => $filters['sales_person_id'] ?? null,
                'item_group_id' => $filters['item_group_id'] ?? null,
                'warehouse_id' => $filters['warehouse_id'] ?? null,
            ],
            'summary' => [
                'total_target_amount' => round($uniqueRows->sum('target_amount'), 2),
                'total_monthly_target' => round(array_sum(array_column($rows, 'monthly_target')), 2),
                'total_period_target' => round($uniqueRows->sum('total_period_target'), 2),
                'total_actual_sales' => round($uniqueRows->sum('total_actual_sales'), 2),
                'total_commission_amount' => round(array_sum(array_column($rows, 'commission_amount')), 2),
                'total_invoice_count' => array_sum(array_column($rows, 'invoice_count')),
            ],
            'data' => $rows,
        ];
    }

   public function postCommission(int $companyId, array $filters): array
{
    return DB::transaction(function () use ($companyId, $filters) {
        $report = $this->report($companyId, $filters);

        $posted = [];
        $skipped = [];

        $groups = collect($report['data'])->groupBy('target_id');

        foreach ($groups as $targetId => $rows) {
            $firstRow = $rows->first();

            if (!empty($firstRow['data_error'])) {
                $skipped[] = [
                    'target_id' => $targetId,
                    'reason' => $firstRow['data_error'],
                ];
                continue;
            }

            if (!$firstRow['period_is_finished']) {
                $skipped[] = [
                    'target_id' => $targetId,
                    'reason' => 'Distribution period is not finished.',
                ];
                continue;
            }

            if (!$firstRow['target_achieved']) {
                $skipped[] = [
                    'target_id' => $targetId,
                    'reason' => 'Target not achieved.',
                ];
                continue;
            }

            $commissionAmount = round(
                (float) $firstRow['total_actual_sales'] * ((float) $firstRow['commission_rate'] / 100),
                2
            );

            if ($commissionAmount <= 0) {
                $skipped[] = [
                    'target_id' => $targetId,
                    'reason' => 'Commission amount is zero.',
                ];
                continue;
            }

            $salesPerson = DB::table('sales_people')
                ->where('id', $firstRow['sales_person_id'])
                ->first();

            if (!$salesPerson?->employee_id) {
                throw new RuntimeException('Sales person is not linked to an employee.');
            }

            if ($salesPerson->use_default_account) {
                $settings = DB::table('company_account_settings')
                    ->where('company_id', $companyId)
                    ->first();

                if (
                    !$settings ||
                    !$settings->default_direct_expense_account_id ||
                    !$settings->default_payable_account_id
                ) {
                    throw new RuntimeException('Default commission posting accounts are not configured.');
                }

                $commissionAccountId = $settings->default_direct_expense_account_id;
                $payrollAccountId = $settings->default_payable_account_id;
            } else {
                if (
                    !$salesPerson->commission_account_id ||
                    !$salesPerson->payroll_account_id
                ) {
                    throw new RuntimeException('Manual commission posting accounts are not configured.');
                }

                $commissionAccountId = $salesPerson->commission_account_id;
                $payrollAccountId = $salesPerson->payroll_account_id;
            }

            $description = $this->commissionDescription($firstRow, $filters);

            $existingEntry = JournalEntry::query()
                ->where('company_id', $companyId)
                ->where('source_type', 'sales_person_commission')
                ->where('description', $description)
                ->whereNull('cancelled_at')
                ->first();

            if ($existingEntry) {
                $skipped[] = [
                    'target_id' => $targetId,
                    'reason' => 'Commission already posted.',
                    'journal_entry_number' => $existingEntry->entry_number,
                ];
                continue;
            }

            $entry = JournalEntry::query()->create([
                'company_id' => $companyId,
                'entry_number' => $this->nextJournalEntryNumber($companyId, $filters['to_date']),
                'entry_date' => $filters['to_date'],
                'total_debit' => $commissionAmount,
                'total_credit' => $commissionAmount,
                'posted_at' => now(),
                'description' => $description,
                'source_type' => 'sales_person_commission',
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            JournalEntryLine::query()->create([
                'company_id' => $companyId,
                'journal_entry_id' => $entry->id,
                'account_id' => $commissionAccountId,
                'party_type' => null,
                'party_id' => null,
                'debit' => $commissionAmount,
                'credit' => 0,
                'note' => 'Sales person commission expense',
            ]);

            JournalEntryLine::query()->create([
                'company_id' => $companyId,
                'journal_entry_id' => $entry->id,
                'account_id' => $payrollAccountId,
                'party_type' => 'employee',
                'party_id' => $salesPerson->employee_id,
                'debit' => 0,
                'credit' => $commissionAmount,
                'note' => 'Sales person commission payable',
            ]);

            EmployeeAllowance::query()->updateOrCreate(
                [
                    'employee_id' => $salesPerson->employee_id,
                    'name_ar' => 'بدل عمولة',
                    'name_en' => 'Commission Allowance',
                ],
                [
                    'amount' => $commissionAmount,
                ]
            );

            $posted[] = [
                'target_id' => $targetId,
                'employee_id' => $salesPerson->employee_id,
                'commission_amount' => $commissionAmount,
                'journal_entry_number' => $entry->entry_number,
            ];
        }

        return [
            'posted' => $posted,
            'skipped' => $skipped,
        ];
    });
}
    private function distributionLines(int $monthlyDistributionId): Collection
    {
        return DB::table('monthly_distribution_lines')
            ->where('monthly_distribution_id', $monthlyDistributionId)
            ->where('percentage', '>', 0)
            ->orderBy('month')
            ->get(['month', 'percentage']);
    }

    private function monthlyTargets(float $targetAmount, Collection $lines): array
    {
        return $lines->map(function ($line) use ($targetAmount) {
            return [
                'month' => (int) $line->month,
                'percentage' => round((float) $line->percentage, 2),
                'monthly_target' => round($targetAmount * ((float) $line->percentage / 100), 2),
            ];
        })->values()->toArray();
    }

    private function periodIsFinished(string $toDate, Collection $lines): bool
    {
        if ($lines->isEmpty()) {
            return false;
        }

        $lastMonth = (int) $lines->max('month');
        $year = Carbon::parse($toDate)->year;

        $distributionEndDate = Carbon::create($year, $lastMonth, 1)->endOfMonth();

        return Carbon::parse($toDate)->endOfDay()->greaterThanOrEqualTo($distributionEndDate);
    }

    private function getActualSalesByMonth(
        int $companyId,
        int $salesPersonId,
        int $itemGroupId,
        int $month,
        string $fromDate,
        string $toDate,
        ?int $warehouseId
    ): float {
        return round((float) $this->baseSalesQuery(
            $companyId,
            $salesPersonId,
            $itemGroupId,
            $fromDate,
            $toDate,
            $warehouseId
        )
            ->whereMonth('si.posting_date', $month)
            ->sum('sii.amount'), 2);
    }

    private function getTotalActualSales(
        int $companyId,
        int $salesPersonId,
        int $itemGroupId,
        string $fromDate,
        string $toDate,
        ?int $warehouseId
    ): float {
        return round((float) $this->baseSalesQuery(
            $companyId,
            $salesPersonId,
            $itemGroupId,
            $fromDate,
            $toDate,
            $warehouseId
        )->sum('sii.amount'), 2);
    }

    private function getInvoiceCountByMonth(
        int $companyId,
        int $salesPersonId,
        int $itemGroupId,
        int $month,
        string $fromDate,
        string $toDate,
        ?int $warehouseId
    ): int {
        return $this->baseSalesQuery(
            $companyId,
            $salesPersonId,
            $itemGroupId,
            $fromDate,
            $toDate,
            $warehouseId
        )
            ->whereMonth('si.posting_date', $month)
            ->distinct('si.id')
            ->count('si.id');
    }

    private function baseSalesQuery(
        int $companyId,
        int $salesPersonId,
        int $itemGroupId,
        string $fromDate,
        string $toDate,
        ?int $warehouseId
    ) {
        return DB::table('sales_invoices as si')
            ->join('sales_invoice_items as sii', 'sii.sales_invoice_id', '=', 'si.id')
            ->join('items as i', 'i.id', '=', 'sii.item_id')
            ->where('si.company_id', $companyId)
            ->where('si.status', 'submitted')
            ->whereNull('si.deleted_at')
            ->where('si.sales_person_id', $salesPersonId)
            ->where('i.item_group_id', $itemGroupId)
            ->whereBetween('si.posting_date', [$fromDate, $toDate])
            ->when($warehouseId, fn ($q) => $q->where('sii.warehouse_id', $warehouseId));
    }

    private function commissionDescription(array $row, array $filters): string
    {
        return 'Sales Person Commission | Target: ' . $row['target_id']
            . ' | Sales Person: ' . $row['sales_person_id']
            . ' | Item Group: ' . $row['item_group_id']
            . ' | From: ' . $filters['from_date']
            . ' | To: ' . $filters['to_date'];
    }

    private function existingJournalEntryNumber(int $companyId, array $row, array $filters): ?string
    {
        $description = $this->commissionDescription($row, $filters);

        return JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('source_type', 'sales_person_commission')
            ->where('description', $description)
            ->whereNull('cancelled_at')
            ->value('entry_number');
    }

   private function nextJournalEntryNumber(int $companyId, string $entryDate): string
{
    $year = Carbon::parse($entryDate)->year;

    $lastNumber = JournalEntry::query()
        ->where('company_id', $companyId)
        ->whereYear('entry_date', $year)
        ->where('entry_number', 'like', 'JV-' . $year . '-%')
        ->selectRaw("
            MAX(CAST(RIGHT(entry_number, 5) AS INT)) as max_number
        ")
        ->value('max_number');

    $nextNumber = ((int) $lastNumber) + 1;

    do {
        $entryNumber = 'JV-' . $year . '-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);

        $exists = JournalEntry::query()
            ->where('company_id', $companyId)
            ->where('entry_number', $entryNumber)
            ->exists();

        if (!$exists) {
            return $entryNumber;
        }

        $nextNumber++;
    } while (true);
}
}