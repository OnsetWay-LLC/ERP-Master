<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\WarehouseWiseStockBalanceReportRequest;
use App\Services\Reports\WarehouseWiseStockBalanceReportService;
use Mpdf\Mpdf;

class WarehouseWiseStockBalanceReportController extends Controller
{
    public function __construct(
        private WarehouseWiseStockBalanceReportService $service
    ) {}

    public function report(WarehouseWiseStockBalanceReportRequest $request)
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->generate($request->validated()),
        ]);
    }

    public function pdf(WarehouseWiseStockBalanceReportRequest $request)
    {
        $filters = $request->validated();
        $data = $this->service->generate($filters);

        $html = view('pdf.warehouse-wise-stock-balance-report', [
            'rows' => $data['rows'],
            'totals' => $data['totals'],
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