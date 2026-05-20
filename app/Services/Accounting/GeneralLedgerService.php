<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use Illuminate\Support\Collection;

class GeneralLedgerService
{
    public function accountsDropdown(int $companyId): Collection
    {
        return ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_number')
            ->get([
                'id',
                'parent_id',
                'account_number',
                'name_ar',
                'name_en',
                'account_level',
                'account_type',
                'root_category',
                'sub_category',
            ]);
    }

    public function report(int $companyId, array $filters): array
    {
        $accountId = (int) $filters['account_id'];
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $account = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('id', $accountId)
            ->firstOrFail();

        $accountIds = $this->getLedgerAccountIds($account, $companyId);

        $openingBalance = $this->openingBalance(
            companyId: $companyId,
            accountIds: $accountIds,
            fromDate: $fromDate
        );

        $entries = GeneralLedger::query()
            ->where('company_id', $companyId)
            ->whereIn('account_id', $accountIds)
            ->with([
                'journalEntry',
                'creator',
                'account',
            ])
            ->when($fromDate, fn ($q) => $q->whereDate('entry_date', '>=', $fromDate))
            ->when($toDate, fn ($q) => $q->whereDate('entry_date', '<=', $toDate))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $runningBalance = $openingBalance;

        $rows = $entries->map(function ($entry) use (&$runningBalance) {
            $debit = (float) $entry->debit;
            $credit = (float) $entry->credit;

            $runningBalance = ($runningBalance + $debit) - $credit;

            return [
                'id' => $entry->id,

                'date' => $entry->entry_date?->format('Y-m-d'),

                'voucher_no' => $entry->journalEntry?->entry_number,

                'voucher_type' => 'Journal Entry',

                'journal_entry_id' => $entry->journal_entry_id,

                'account' => [
                    'id' => $entry->account?->id,
                    'account_number' => $entry->account?->account_number,
                    'name_ar' => $entry->account?->name_ar,
                    'name_en' => $entry->account?->name_en,
                    'account_level' => $entry->account?->account_level,
                ],

                'created_by' => [
                    'id' => $entry->creator?->id,
                    'name' => $entry->creator?->name,
                ],

                'description' => $entry->description,

                'debit' => $this->money($debit),

                'credit' => $this->money($credit),

                'balance' => $this->formatDrCr($runningBalance),

                'raw_balance' => $this->money($runningBalance),
            ];
        });

        $totalDebit = (float) $entries->sum('debit');
        $totalCredit = (float) $entries->sum('credit');
        $closingBalance = ($openingBalance + $totalDebit) - $totalCredit;

        return [
            'account' => [
                'id' => $account->id,
                'parent_id' => $account->parent_id,
                'account_number' => $account->account_number,
                'name_ar' => $account->name_ar,
                'name_en' => $account->name_en,
                'account_level' => $account->account_level,
                'account_type' => $account->account_type,
                'root_category' => $account->root_category,
                'sub_category' => $account->sub_category,
            ],

            'included_account_ids' => $accountIds,

            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],

            'opening_balance' => [
                'amount' => $this->money(abs($openingBalance)),
                'type' => $openingBalance >= 0 ? 'Dr' : 'Cr',
                'display' => $this->formatDrCr($openingBalance),
                'raw_balance' => $this->money($openingBalance),
            ],

            'rows' => $rows,

            'grand_total' => [
                'total_debit' => $this->money($totalDebit),
                'total_credit' => $this->money($totalCredit),
            ],

            'closing_balance' => [
                'amount' => $this->money(abs($closingBalance)),
                'type' => $closingBalance >= 0 ? 'Dr' : 'Cr',
                'display' => $this->formatDrCr($closingBalance),
                'raw_balance' => $this->money($closingBalance),
            ],
        ];
    }

   private function getLedgerAccountIds(ChartOfAccount $account, int $companyId): array
{
    // إذا الحساب Child رجعيه مباشرة
    if ($account->account_level === 'child') {
        return [$account->id];
    }

    $accountIds = [];

    $this->collectChildAccounts(
        accountId: $account->id,
        companyId: $companyId,
        result: $accountIds
    );

    return $accountIds;
}

    private function openingBalance(int $companyId, array $accountIds, ?string $fromDate): float
    {
        if (! $fromDate) {
            return 0;
        }

        $totalDebit = (float) GeneralLedger::query()
            ->where('company_id', $companyId)
            ->whereIn('account_id', $accountIds)
            ->whereDate('entry_date', '<', $fromDate)
            ->sum('debit');

        $totalCredit = (float) GeneralLedger::query()
            ->where('company_id', $companyId)
            ->whereIn('account_id', $accountIds)
            ->whereDate('entry_date', '<', $fromDate)
            ->sum('credit');

        return $totalDebit - $totalCredit;
    }

    private function formatDrCr(float $balance): string
    {
        if (round($balance, 2) == 0.00) {
            return '0.00';
        }

        $type = $balance >= 0 ? 'Dr' : 'Cr';

        return $this->money(abs($balance)) . ' ' . $type;
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
    private function collectChildAccounts(
    int $accountId,
    int $companyId,
    array &$result
): void {
    $children = ChartOfAccount::query()
        ->where('company_id', $companyId)
        ->where('parent_id', $accountId)
        ->where('is_active', true)
        ->get();

    foreach ($children as $child) {

        // إذا Child نهائي خزنيه
        if ($child->account_level === 'child') {
            $result[] = $child->id;
        }

        // كملي نزول بالشجرة
        $this->collectChildAccounts(
            accountId: $child->id,
            companyId: $companyId,
            result: $result
        );
    }
}
}