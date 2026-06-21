<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\SalesPersonPerformanceReportRequest;
use App\Models\Company;
use App\Services\Reports\SalesPersonPerformanceReportService;
use Illuminate\Http\JsonResponse;
use Mpdf\Mpdf;

class SalesPersonPerformanceReportController extends Controller
{
    public function __construct(
        private readonly SalesPersonPerformanceReportService $service
    ) {
    }

    public function index(SalesPersonPerformanceReportRequest $request): JsonResponse
    {
        $companyId = auth()->user()->company_id
            ?? Company::query()->value('id');

        if (!$companyId) {
            return response()->json([
                'status' => false,
                'message' => 'No company found.',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Sales Person Performance Report generated successfully.',
            'report' => $this->service->report((int) $companyId, $request->validated()),
        ]);
    }

    public function pdf(SalesPersonPerformanceReportRequest $request)
    {
        $companyId = auth()->user()->company_id
            ?? Company::query()->value('id');

        if (!$companyId) {
            return response()->json([
                'status' => false,
                'message' => 'No company found.',
            ], 422);
        }

        $locale = app()->getLocale();

        $report = $this->service->report((int) $companyId, $request->validated());

        $html = view('reports.sales-person-performance-pdf', [
            'locale' => $locale,
            'report' => $report,
            'filters' => $report['filters'],
            'summary' => $report['summary'],
            'rows' => $report['data'],
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'default_font' => 'dejavusans',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 8,
            'margin_right' => 8,
        ]);

        if ($locale === 'ar') {
            $mpdf->SetDirectionality('rtl');
        } else {
            $mpdf->SetDirectionality('ltr');
        }

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('sales-person-performance-report.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="sales-person-performance-report.pdf"');
    }
}