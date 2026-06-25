<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\AccountsPayableReportRequest;
use App\Services\Reports\AccountsPayableReportService;
use Mpdf\Mpdf;

class AccountsPayableReportController extends Controller
{
    public function __construct(
        private AccountsPayableReportService $service
    ) {}

    public function report(AccountsPayableReportRequest $request)
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->generate($request->validated()),
        ]);
    }

    public function pdf(AccountsPayableReportRequest $request)
    {
        $filters = $request->validated();
        $data = $this->service->generate($filters);

        $html = view('reports.accounts-payable-report', [
            'rows' => $data['rows'],
            'totals' => $data['totals'],
            'fromDate' => $filters['from_date'],
            'toDate' => $filters['to_date'],
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf');
    }
}