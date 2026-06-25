<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\StockLedgerReportRequest;
use App\Services\Reports\StockLedgerReportService;
use Mpdf\Mpdf;


class StockLedgerReportController extends Controller
{
    public function __construct(
        private StockLedgerReportService $service
    ) {}

    public function index(StockLedgerReportRequest $request)
    {
        return response()->json(
            $this->service->generate($request->validated())
        );
    }
    public function pdf(StockLedgerReportRequest $request)
{
    $result = $this->service->generateForPdf($request->validated());

    $html = view('reports.stock-ledger-report', [
        'rows' => $result['rows'],
        'filters' => $result['filters'],
    ])->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4-L',
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
    ]);

    $mpdf->WriteHTML($html);

    return response($mpdf->Output('stock-ledger-report.pdf', 'S'), 200)
        ->header('Content-Type', 'application/pdf');
}
}