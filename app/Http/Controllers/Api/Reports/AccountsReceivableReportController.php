<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\AccountsReceivableReportRequest;
use App\Services\Reports\AccountsReceivableReportService;
use Illuminate\Http\Response;
use Mpdf\Mpdf;

class AccountsReceivableReportController extends Controller
{
    public function __construct(
        private AccountsReceivableReportService $service
    ) {}

    public function pdf(AccountsReceivableReportRequest $request): Response
    {
        $data = $this->service->generate($request->validated());

        $html = view('reports.accounts-receivable-pdf', [
            'rows' => $data['rows'],
            'totals' => $data['totals'],
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

        return response($mpdf->Output('accounts-receivable-report.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="accounts-receivable-report.pdf"',
        ]);
    }
}