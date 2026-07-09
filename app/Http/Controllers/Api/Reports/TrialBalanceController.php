<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\TrialBalanceRequest;
use App\Services\Reports\TrialBalanceService;
use Illuminate\Http\JsonResponse;
use Mpdf\Mpdf;
use App\Models\Company;
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
    $company = Company::query()->firstOrFail();

    $data = $this->service->generate(
        $this->companyId(),
        $request->validated('from_date'),
        $request->validated('to_date')
    );

    $html = View::make('pdf.trial-balance', [
        'company' => $company,
        'report' => $data,
    ])->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4-L',
        'default_font' => 'dejavusans',
        'margin_top' => 8,
        'margin_bottom' => 18,
        'margin_left' => 8,
        'margin_right' => 8,
    ]);

    if (app()->getLocale() === 'ar') {
        $mpdf->SetDirectionality('rtl');
    }

    $this->applySystemFooter($mpdf);

    $mpdf->WriteHTML($html);

    return response($mpdf->Output('trial-balance.pdf', 'S'), 200)
        ->header('Content-Type', 'application/pdf');
}

private function applySystemFooter(Mpdf $mpdf): void
{
    $footerImage = app()->getLocale() === 'ar'
        ? public_path('images/reports/system-footer-ar.png')
        : public_path('images/reports/system-footer-en.png');

    $mpdf->SetHTMLFooter('
        <div style="text-align:center; padding-top:4px;">
            <img src="' . $footerImage . '" style="width:100%; max-width:650px;">
        </div>
    ');
}
}