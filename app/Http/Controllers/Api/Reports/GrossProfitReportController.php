<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\GrossProfitReportRequest;
use App\Services\Reports\GrossProfitReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class GrossProfitReportController extends Controller
{
    public function __construct(
        private readonly GrossProfitReportService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function report(GrossProfitReportRequest $request): JsonResponse
    {
        $data = $this->service->generate(
            $this->companyId(),
            $request->validated('from_date'),
            $request->validated('to_date'),
            $request->validated('filter_by')
        );

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function exportPdf(GrossProfitReportRequest $request)
    {
        $data = $this->service->generate(
            $this->companyId(),
            $request->validated('from_date'),
            $request->validated('to_date'),
            $request->validated('filter_by')
        );

        if (empty($data['rows'])) {
            return response()->json([
                'status' => false,
                'message' => 'No data found.',
            ], 404);
        }

        $html = View::make('reports.gross-profit', [
            'report' => $data,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L',
            'default_font' => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('gross-profit-report.pdf', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="gross-profit-report.pdf"',
            ]
        );
    }
}