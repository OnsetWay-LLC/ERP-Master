<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ItemWiseSalesRegisterReportRequest;
use App\Services\Reports\ItemWiseSalesRegisterReportService;
use Illuminate\Http\Response;
use Mpdf\Mpdf;

class ItemWiseSalesRegisterReportController extends Controller
{
    public function __construct(
        private ItemWiseSalesRegisterReportService $service
    ) {}

    public function pdf(ItemWiseSalesRegisterReportRequest $request): Response
    {
        $data = $this->service->generate($request->validated());

        $html = view('reports.item-wise-sales-register-pdf', [
            'rows' => $data['rows'],
            'totals' => $data['totals'],
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_top' => 8,
            'margin_bottom' => 8,
            'margin_left' => 8,
            'margin_right' => 8,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('item-wise-sales-register-report.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="item-wise-sales-register-report.pdf"',
        ]);
    }
}