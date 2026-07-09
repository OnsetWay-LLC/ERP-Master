<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ProfitLossRequest;
use App\Services\Reports\ProfitLossService;
use Illuminate\Http\JsonResponse;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\View;
use App\Models\Company;
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
    $company = Company::query()->firstOrFail();

    $data = $this->service->generate(
        $this->companyId(),
        (int) $request->validated('financial_year')
    );

    $html = View::make('pdf.profit-loss', [
        'company' => $company,
        'report' => $data,
    ])->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
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

    return response($mpdf->Output('profit-loss.pdf', 'S'), 200)
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