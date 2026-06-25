<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FinancialYear;
use App\Services\Reports\ProfitLossService;
use App\Services\Reports\TrialBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class BalanceSheetReportController extends Controller
{
    public function __construct(
        private readonly TrialBalanceService $trialBalanceService,
        private readonly ProfitLossService $profitLossService
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function pdf(Request $request)
    {
        $request->validate([
            'financial_year_id' => ['required', 'integer', 'exists:financial_years,id'],
        ]);

        $companyId = $this->companyId();

        $company = Company::query()->findOrFail($companyId);

        $financialYear = FinancialYear::query()
            ->where('company_id', $companyId)
            ->findOrFail($request->financial_year_id);

        $fromDate = $financialYear->start_date->format('Y-m-d');
        $toDate = $financialYear->end_date->format('Y-m-d');

        $trialBalance = $this->trialBalanceService->generate(
            $companyId,
            $fromDate,
            $toDate
        );

        $profitLoss = $this->profitLossService->generate(
            $companyId,
            (int) $financialYear->start_date->format('Y')
        );

        $assets = [];
        $liabilities = [];
        $equity = [];

        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquityOnly = 0;

        $assetTypes = [
            'assets',
            'bank',
            'cash',
            'receivable',
            'stock',
            'fixed_asset',
            'current_asset',
            'non_current_asset',
            'input_tax',
        ];

        $liabilityTypes = [
            'liabilities',
            'payable',
            'liability',
            'current_liability',
            'non_current_liability',
            'output_tax',
        ];

        $equityTypes = [
            'equity',
            'capital',
            'owner_equity',
            'retained_earnings',
            'dividends_paid',
        ];

        foreach ($trialBalance['child_rows'] as $row) {
            $accountType = $row['account_type'];

            $debit = (float) $row['debit_balance'];
            $credit = (float) $row['credit_balance'];

            if (in_array($accountType, $assetTypes, true)) {
                $value = $debit - $credit;

                if ($value != 0) {
                    $assets[] = [
                        'account_number' => $row['account_number'],
                        'account_name_ar' => $row['account_name_ar'],
                        'account_name_en' => $row['account_name_en'],
                        'value' => round($value, 2),
                    ];

                    $totalAssets += $value;
                }

                continue;
            }

            if (in_array($accountType, $liabilityTypes, true)) {
                $value = $credit - $debit;

                if ($value != 0) {
                    $liabilities[] = [
                        'account_number' => $row['account_number'],
                        'account_name_ar' => $row['account_name_ar'],
                        'account_name_en' => $row['account_name_en'],
                        'value' => round($value, 2),
                    ];

                    $totalLiabilities += $value;
                }

                continue;
            }

            if (in_array($accountType, $equityTypes, true)) {
                if ($accountType === 'dividends_paid') {
                    $rawValue = $debit - $credit;
                    $value = -abs($rawValue);
                } else {
                    $value = $credit - $debit;
                }

                if ($value != 0) {
                    $equity[] = [
                        'account_number' => $row['account_number'],
                        'account_name_ar' => $row['account_name_ar'],
                        'account_name_en' => $row['account_name_en'],
                        'value' => round($value, 2),
                    ];

                    $totalEquityOnly += $value;
                }
            }
        }

        $netProfitLoss = (float) $profitLoss['net_result'];

        $totalAssets = round($totalAssets, 2);
        $totalLiabilities = round($totalLiabilities, 2);
        $totalEquityOnly = round($totalEquityOnly, 2);

        $totalEquity = round($totalEquityOnly + $netProfitLoss, 2);
        $liabilitiesAndEquity = round($totalLiabilities + $totalEquity, 2);

        $difference = round($totalAssets - $liabilitiesAndEquity, 2);
        $isBalanced = $difference == 0.00;

        $hasData =
            count($assets) > 0 ||
            count($liabilities) > 0 ||
            count($equity) > 0 ||
            $netProfitLoss != 0;

        $report = [
            'company' => $company,
            'financial_year' => $financialYear,

            'from_date' => $fromDate,
            'to_date' => $toDate,

            'assets' => [
                'rows' => $assets,
                'total' => $totalAssets,
            ],

            'liabilities' => [
                'rows' => $liabilities,
                'total' => $totalLiabilities,
            ],

            'equity' => [
                'rows' => $equity,
                'total_without_profit_loss' => $totalEquityOnly,
                'net_profit_loss' => round($netProfitLoss, 2),
                'total' => $totalEquity,
            ],

            'equation' => [
                'assets' => $totalAssets,
                'liabilities_and_equity' => $liabilitiesAndEquity,
                'difference' => $difference,
                'is_balanced' => $isBalanced,
            ],

            'has_data' => $hasData,

            'message' => ! $hasData
                ? 'لا توجد بيانات'
                : ($isBalanced
                    ? 'Balance Sheet is balanced.'
                    : 'Balance Sheet is not balanced.'),
        ];

        $html = View::make('reports.balance-sheet', [
            'report' => $report,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('balance-sheet.pdf', 'S'),
            200
        )->header('Content-Type', 'application/pdf');
    }
}