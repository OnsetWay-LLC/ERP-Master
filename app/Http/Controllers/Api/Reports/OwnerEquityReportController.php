<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\OwnerEquityReportRequest;
use App\Services\Reports\OwnerEquityReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class OwnerEquityReportController extends Controller
{
    public function __construct(
        private readonly OwnerEquityReportService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function report(OwnerEquityReportRequest $request): JsonResponse
    {
        $data = $this->service->generate(
            $this->companyId(),
            (int) $request->validated('financial_year')
        );

        if ($data['message'] === 'No data found.') {
            return response()->json([
                'status' => false,
                'message' => 'No data found.',
                'data' => $data,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function exportPdf(OwnerEquityReportRequest $request)
    {
        $data = $this->service->generate(
            $this->companyId(),
            (int) $request->validated('financial_year')
        );

        if ($data['message'] === 'No data found.') {
            return response()->json([
                'status' => false,
                'message' => 'No data found.',
            ], 404);
        }

        $html = View::make('reports.owner-equity', [
            'report' => $data,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font' => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('owner-equity-report.pdf', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="owner-equity-report.pdf"',
            ]
        );
    }
}