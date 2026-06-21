<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\SupplierLedgerReportRequest;
use App\Services\Reports\SupplierLedgerReportService;
use Illuminate\Http\Response;
use Mpdf\Mpdf;

class SupplierLedgerReportController extends Controller
{
    public function __construct(
        private SupplierLedgerReportService $service
    ) {}

    public function pdf(SupplierLedgerReportRequest $request): Response
    {
        $data = $this->service->generate($request->validated());

        $html = view('reports.supplier-ledger-pdf', [
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

        return response($mpdf->Output('supplier-ledger-report.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="supplier-ledger-report.pdf"',
        ]);
    }
}