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

        $rows = collect();

       foreach ($balances as $accountId => $balance) {
            $account = $accounts[$accountId] ?? null;

            if (! $account) {
                continue;
            }
if ($account->account_level !== 'parent') {
    continue;
}
            if ($balance > 0) {
                $debitBalance = $balance;
                $creditBalance = 0;
            } else {
                $debitBalance = 0;
                $creditBalance = abs($balance);
            }

            $rows->push([
                'account_id' => $account->id,
                'parent_id' => $account->parent_id,
                'account_number' => $account->account_number,
                'account_name_ar' => $account->name_ar,
                'account_name_en' => $account->name_en,
                'account_type' => $account->account_type,
                'account_level' => $account->account_level,
                'debit_balance' => round($debitBalance, 2),
                'credit_balance' => round($creditBalance, 2),
            ]);
        }

        $rows = $rows
            ->sortBy('account_number')
            ->values();

        $totalDebit = round($rows->sum('debit_balance'), 2);
        $totalCredit = round($rows->sum('credit_balance'), 2);
        $difference = round($totalDebit - $totalCredit, 2);

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,

            'rows' => $rows,

            'totals' => [
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'difference' => $difference,
            ],

            'is_balanced' => $difference == 0.00,

            'message' => $rows->isEmpty()
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