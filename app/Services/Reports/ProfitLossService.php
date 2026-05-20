<?php

namespace App\Services\Reports;

class ProfitLossService
{
    public function __construct(
        private readonly TrialBalanceService $trialBalanceService
    ) {}

    public function generate(int $companyId, int $financialYear): array
    {
        $fromDate = $financialYear . '-01-01';
        $toDate = $financialYear . '-12-31';

        $trialBalance = $this->trialBalanceService->generate(
            $companyId,
            $fromDate,
            $toDate
        );

        $incomeTypes = [
            'income',
            'direct_income',
            'indirect_income',
        ];

        $expenseTypes = [
            'expenses',
            'cogs',
            'direct_expense',
            'indirect_expense',
            'depreciation',
            'expenses_included_in_valuation',
        ];

        $incomeRows = [];
        $expenseRows = [];

        $totalIncome = 0;
        $totalExpenses = 0;

        foreach ($trialBalance['rows'] as $row) {
            $accountType = $row['account_type'];

            if (in_array($accountType, $incomeTypes, true)) {
                $value = (float) $row['credit_balance'] - (float) $row['debit_balance'];

                if ($value != 0) {
                    $incomeRows[] = [
                        'account_id' => $row['account_id'],
                        'account_number' => $row['account_number'],
                        'account_name_ar' => $row['account_name_ar'],
                        'account_name_en' => $row['account_name_en'],
                        'account_type' => $accountType,
                        'value' => round(abs($value), 2),
                    ];

                    $totalIncome += abs($value);
                }
            }

            if (in_array($accountType, $expenseTypes, true)) {
                $value = (float) $row['debit_balance'] - (float) $row['credit_balance'];

                if ($value != 0) {
                    $expenseRows[] = [
                        'account_id' => $row['account_id'],
                        'account_number' => $row['account_number'],
                        'account_name_ar' => $row['account_name_ar'],
                        'account_name_en' => $row['account_name_en'],
                        'account_type' => $accountType,
                        'value' => round(abs($value), 2),
                    ];

                    $totalExpenses += abs($value);
                }
            }
        }

        $netResult = round($totalIncome - $totalExpenses, 2);

        return [
            'financial_year' => $financialYear,
            'from_date' => $fromDate,
            'to_date' => $toDate,

            'income' => [
                'rows' => $incomeRows,
                'total_income' => round($totalIncome, 2),
            ],

            'expenses' => [
                'rows' => $expenseRows,
                'total_expenses' => round($totalExpenses, 2),
            ],

            'net_result' => $netResult,

            'result_type' => $netResult >= 0
                ? 'profit'
                : 'loss',

            'message' => empty($incomeRows) && empty($expenseRows)
                ? 'No data found.'
                : ($netResult >= 0 ? 'Net Profit.' : 'Net Loss.'),
        ];
    }
}