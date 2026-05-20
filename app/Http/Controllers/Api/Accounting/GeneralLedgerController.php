<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Accounting\GeneralLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Exports\GeneralLedgerExport;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class GeneralLedgerController extends Controller
{
    public function __construct(
        private readonly GeneralLedgerService $service
    ) {
    }

    private function companyId(): int
    {
        $companyId = auth('api')->user()->company_id ?? Company::query()->value('id');

        if (! $companyId) {
            abort(422, 'No company found.');
        }

        return (int) $companyId;
    }

    public function accountsDropdown(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $this->service->accountsDropdown($this->companyId()),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        return response()->json([
            'status' => true,
            'data' => $this->service->report(
                companyId: $this->companyId(),
                filters: $filters
            ),
        ]);
    }
    public function exportExcel(Request $request)
{
    $filters = $request->validate([
        'account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
        'from_date' => ['nullable', 'date'],
        'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
    ]);

    return Excel::download(
        new GeneralLedgerExport($this->companyId(), $filters),
        'general-ledger.xlsx'
    );
}

public function exportPdf(Request $request)
{
    $filters = $request->validate([
        'account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
        'from_date' => ['nullable', 'date'],
        'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
    ]);

    $report = $this->service->report($this->companyId(), $filters);

    $html = view('pdf.general-ledger', compact('report'))->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
    ]);

    $mpdf->WriteHTML($html);

    return response($mpdf->Output('', 'S'))
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename=\"general-ledger.pdf\"');
}
}