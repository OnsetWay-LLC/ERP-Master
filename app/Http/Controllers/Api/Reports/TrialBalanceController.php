<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\TrialBalanceRequest;
use App\Services\Reports\TrialBalanceService;
use Illuminate\Http\JsonResponse;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\View;

class TrialBalanceController extends Controller
{
    public function __construct(
        private readonly TrialBalanceService $service
    ) {}

    private function companyId(): int
    {
        return 1;
    }

    public function report(TrialBalanceRequest $request): JsonResponse
    {
        $data = $this->service->generate(
            $this->companyId(),
            $request->validated('from_date'),
            $request->validated('to_date')
        );

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
    public function exportPdf(TrialBalanceRequest $request)
{
    $data = $this->service->generate(
        $this->companyId(),
        $request->validated('from_date'),
        $request->validated('to_date')
    );

    $html = View::make(
        'pdf.trial-balance',
        [
            'report' => $data,
        ]
    )->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'L',
    ]);

    $mpdf->SetDirectionality('rtl');

    $mpdf->WriteHTML($html);

    return response(
        $mpdf->Output(
            'trial-balance.pdf',
            'S'
        ),
        200
    )->header('Content-Type', 'application/pdf');
}
}