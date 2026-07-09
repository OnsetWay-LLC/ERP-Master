<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Reports\PayrollReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class PayrollReportController extends Controller
{
    public function __construct(
        private readonly PayrollReportService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
        ]);

        $companyId = auth('api')->user()->company_id
            ?? Company::query()->value('id');

        return response()->json([
            'status' => true,
            'message' => 'Payroll report generated successfully.',
            'report' => $this->service->report((int) $companyId, $data),
        ]);
    }

    public function pdf(Request $request)
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
        ]);

        $company = Company::query()->firstOrFail();

        $report = $this->service->report((int) $company->id, $data);

        $html = view('reports.payroll-report', [
            'company' => $company,
            'report' => $report,
        ])->render();

       $mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L',
    'default_font' => 'dejavusans',
    'margin_top' => 10,
    'margin_bottom' => 18,
    'margin_left' => 8,
    'margin_right' => 8,
]);

$this->applySystemFooter($mpdf);

$mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="payroll-report.pdf"');
    }

    public function postJournalEntries(Request $request): JsonResponse
    {
        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
        ]);

        $companyId = auth('api')->user()->company_id
            ?? Company::query()->value('id');

        $result = $this->service->postJournalEntries((int) $companyId, $data);

        return response()->json([
            'status' => true,
            'message' => 'Payroll journal entries posted successfully.',
            'data' => $result,
        ]);
    }
    public function range(Request $request): JsonResponse
{
    $data = $request->validate([
        'from_date' => ['required', 'date'],
        'to_date' => ['required', 'date', 'after_or_equal:from_date'],
    ]);

    $companyId = auth('api')->user()->company_id
        ?? Company::query()->value('id');

    return response()->json([
        'status' => true,
        'message' => 'Payroll report generated successfully.',
        'report' => $this->service->reportByDateRange((int) $companyId, $data),
    ]);
}
public function rangePdf(Request $request)
{
    $data = $request->validate([
        'from_date' => ['required', 'date'],
        'to_date' => ['required', 'date', 'after_or_equal:from_date'],
    ]);

    $company = Company::query()->firstOrFail();

    $report = $this->service->reportByDateRange((int) $company->id, $data);

    $html = view('reports.payroll-report', [
        'company' => $company,
        'report' => $report,
    ])->render();

   $mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L',
    'default_font' => 'dejavusans',
    'margin_top' => 10,
    'margin_bottom' => 18,
    'margin_left' => 8,
    'margin_right' => 8,
]);

$this->applySystemFooter($mpdf);

$mpdf->WriteHTML($html);

    return response($mpdf->Output('', 'S'))
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="payroll-report-range.pdf"');
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