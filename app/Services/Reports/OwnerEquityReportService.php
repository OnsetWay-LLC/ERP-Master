<?php

namespace App\Services\Reports;

class OwnerEquityReportService
{
    public function __construct(
        private readonly TrialBalanceService $trialBalanceService,
        private readonly ProfitLossService $profitLossService
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

        $profitLoss = $this->profitLossService->generate(
            $companyId,
            $financialYear
        );

        $capital = $this->sumByAccountTypes(
            $trialBalance['rows'],
            ['capital'],
            'credit'
        );

        $drawings = $this->sumByAccountTypes(
            $trialBalance['rows'],
            ['dividends_paid'],
            'debit'
        );

        $netResult = (float) $profitLoss['net_result'];

        $endingEquity = round($capital + $netResult - $drawings, 2);

        $rows = [
            [
                'account' => 'Capital',
                'account_ar' => 'رأس المال',
                'value' => round($capital, 2),
            ],
            [
                'account' => $netResult >= 0 ? 'Net Profit' : 'Net Loss',
                'account_ar' => $netResult >= 0 ? 'صافي الربح' : 'صافي الخسارة',
                'value' => round($netResult, 2),
            ],
            [
                'account' => 'Drawings',
                'account_ar' => 'السحوبات الشخصية',
                'value' => round($drawings, 2),
            ],
        ];

        $hasData =
            abs($capital) > 0 ||
            abs($netResult) > 0 ||
            abs($drawings) > 0;

        return [
            'financial_year' => $financialYear,
            'from_date' => $fromDate,
            'to_date' => $toDate,

            'rows' => $rows,

            'capital' => round($capital, 2),
            'net_result' => round($netResult, 2),
            'drawings' => round($drawings, 2),
            'ending_equity' => $endingEquity,

            'result_type' => $netResult >= 0 ? 'profit' : 'loss',

            'message' => $hasData
                ? 'Owner equity report generated successfully.'
                : 'No data found.',
        ];
    }

    private function sumByAccountTypes($rows, array $accountTypes, string $normalSide): float
    {
        $total = 0;

        foreach ($rows as $row) {
            if (! in_array($row['account_type'], $accountTypes, true)) {
                continue;
            }

            $debit = (float) $row['debit_balance'];
            $credit = (float) $row['credit_balance'];

            if ($normalSide === 'credit') {
                $value = $credit - $debit;
            } else {
                $value = $debit - $credit;
            }

            $total += $value;
        }

        return round(abs($total), 2);
    }
}