<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ItemWisePurchaseRegisterReportRequest;
use App\Services\Reports\ItemWisePurchaseRegisterReportService;
use Mpdf\Mpdf;

class ItemWisePurchaseRegisterReportController extends Controller
{
    public function __construct(
        private ItemWisePurchaseRegisterReportService $service
    ) {}

    public function pdf(
        ItemWisePurchaseRegisterReportRequest $request
    ) {
        $data = $this->service->generate(
            $request->validated()
        );

        $html = view(
            'reports.item-wise-purchase-register-pdf',
            [
                'rows' => $data['rows'],
                'totals' => $data['totals'],
                'fromDate' => $request->from_date,
                'toDate' => $request->to_date,
            ]
        )->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
        ]);

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }
}