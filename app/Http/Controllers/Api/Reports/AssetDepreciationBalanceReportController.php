<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\AssetDepreciationBalanceReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mpdf\Mpdf;

class AssetDepreciationBalanceReportController extends Controller
{
    public function __construct(
        private readonly AssetDepreciationBalanceReportService $service
    ) {}

    private function companyId(): int
    {
        return auth('api')->user()->company_id ?? 1;
    }

    public function pdf(Request $request): Response
    {
        $report = $this->service->generate(
            $this->companyId(),
            $request->only([
                'date_from',
                'date_to',
                'asset_category_id',
            ])
        );

        $locale = $request->query('locale')
            ?? $request->getPreferredLanguage(['ar', 'en', 'both'])
            ?? 'both';

        $html = view('reports.asset_depreciation_balance_pdf', [
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'dateFrom' => $report['date_from'],
            'dateTo' => $report['date_to'],
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'locale' => $locale,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L',
            'direction' => $locale === 'en' ? 'ltr' : 'rtl',
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('asset-depreciation-balance.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="asset-depreciation-balance.pdf"',
        ]);
    }
}