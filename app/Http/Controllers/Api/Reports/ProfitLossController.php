<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ProfitLossRequest;
use App\Services\Reports\ProfitLossService;
use Illuminate\Http\JsonResponse;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\View;

class ProfitLossController extends Controller
{
    public function __construct(
        private readonly ProfitLossService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function report(ProfitLossRequest $request): JsonResponse
    {
        $data = $this->service->generate(
            $this->companyId(),
            (int) $request->validated('financial_year')
        );

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
    public function exportPdf(ProfitLossRequest $request)
{
    $data = $this->service->generate(
        $this->companyId(),
        (int) $request->validated('financial_year')
    );

    $html = View::make('pdf.profit-loss', [
        'report' => $data,
    ])->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
    ]);

    $mpdf->SetDirectionality('rtl');

    $mpdf->WriteHTML($html);

    return response(
        $mpdf->Output('profit-loss.pdf', 'S'),
        200
    )->header('Content-Type', 'application/pdf');
}
}