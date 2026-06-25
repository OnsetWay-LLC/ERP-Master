<?php

namespace App\Services\Reports;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;

class TrialBalanceService
{
    public function generate(int $companyId, string $fromDate, string $toDate): array
    {
        $accounts = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_number')
            ->get()
            ->keyBy('id');

        $childAccounts = $accounts
            ->where('account_level', 'child');

        $balances = [];

        foreach ($childAccounts as $account) {
            $lastLedger = GeneralLedger::query()
                ->where('company_id', $companyId)
                ->where('account_id', $account->id)
                ->whereBetween('entry_date', [$fromDate, $toDate])
                ->latest('entry_date')
                ->latest('id')
                ->first();

            if (! $lastLedger) {
                continue;
            }

            $balance = (float) $lastLedger->balance;

            if ($balance == 0) {
                continue;
            }

            $this->addBalanceToAccountAndParents(
                $balances,
                $accounts,
                $account,
                $balance
            );
        }

        $parentRows = collect();
        $childRows = collect();

        foreach ($balances as $accountId => $balance) {
            $account = $accounts[$accountId] ?? null;

            if (! $account) {
                continue;
            }

            if ($balance > 0) {
                $debitBalance = $balance;
                $creditBalance = 0;
            } else {
                $debitBalance = 0;
                $creditBalance = abs($balance);
            }

            $row = [
                'account_id' => $account->id,
                'parent_id' => $account->parent_id,
                'account_number' => $account->account_number,
                'account_name_ar' => $account->name_ar,
                'account_name_en' => $account->name_en,
                'root_category' => $account->root_category,
                'sub_category' => $account->sub_category,
                'account_type' => $account->account_type,
                'account_level' => $account->account_level,
                'debit_balance' => round($debitBalance, 2),
                'credit_balance' => round($creditBalance, 2),
            ];

            if ($account->account_level === 'parent') {
                $parentRows->push($row);
            }

            if ($account->account_level === 'child') {
                $childRows->push($row);
            }
        }

        $parentRows = $parentRows
            ->sortBy('account_number')
            ->values();

        $childRows = $childRows
            ->sortBy('account_number')
            ->values();

        $totalDebit = round($parentRows->sum('debit_balance'), 2);
        $totalCredit = round($parentRows->sum('credit_balance'), 2);
        $difference = round($totalDebit - $totalCredit, 2);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,

            // عشان ما نخرب أي تقرير قديم
            'rows' => $parentRows,

            // الجديد
            'parent_rows' => $parentRows,
            'child_rows' => $childRows,

            'totals' => [
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'difference' => $difference,
            ],

            'is_balanced' => $difference == 0.00,

            'message' => $parentRows->isEmpty()
                ? 'No data found.'
                : ($difference == 0.00
                    ? 'Trial balance is balanced.'
                    : 'Trial balance is not balanced.'),
        ];
    }

    private function addBalanceToAccountAndParents(
        array &$balances,
        $accounts,
        ChartOfAccount $account,
        float $balance
    ): void {
        $current = $account;

        while ($current) {
            if (! isset($balances[$current->id])) {
                $balances[$current->id] = 0;
            }

            $balances[$current->id] += $balance;

            if (! $current->parent_id) {
                break;
            }

            $current = $accounts[$current->parent_id] ?? null;
        }
    }
}