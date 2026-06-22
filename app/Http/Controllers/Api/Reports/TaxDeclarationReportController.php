<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ViewTaxDeclarationReportRequest;
use App\Services\Reports\TaxDeclarationReportService;
use Mpdf\Mpdf;

class TaxDeclarationReportController extends Controller
{
    public function __construct(
        private TaxDeclarationReportService $service
    ) {}

 public function pdf(ViewTaxDeclarationReportRequest $request)
{
    $data = $this->service->generate(
        (int) $request->year
    );

    if (
        empty($data['rows']) ||
        (
            (float) $data['output_tax'] == 0 &&
            (float) $data['input_tax'] == 0
        )
    ) {
        return response()->json([
            'status' => false,
            'message' => 'لا توجد بيانات',
        ], 404);
    }

    $html = view('reports.tax-declaration', [
        'data' => $data,
    ])->render();

    $pdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'default_font' => 'dejavusans',
    ]);

    $pdf->WriteHTML($html);

    return response(
        $pdf->Output('Tax Declaration Report.pdf', 'S'),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Tax Declaration Report.pdf"',
        ]
    );
}
}