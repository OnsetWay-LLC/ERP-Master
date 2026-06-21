<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\CustomerLedgerReportRequest;
use App\Services\Reports\CustomerLedgerReportService;
use Illuminate\Http\Response;
use Mpdf\Mpdf;

class CustomerLedgerReportController extends Controller
{
    public function __construct(
        private CustomerLedgerReportService $service
    ) {}

    public function pdf(CustomerLedgerReportRequest $request): Response
    {
        $rows = $this->service->generate($request->validated());

        $html = view('reports.customer-ledger-pdf', [
            'rows' => $rows,
            'fromDate' => $request->from_date,
            'toDate' => $request->to_date,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('customer-ledger-report.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="customer-ledger-report.pdf"',
        ]);
    }
}