<?php

namespace App\Http\Controllers\Api\AuditReport;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditReport\AuditReportRequest;
use App\Http\Resources\AuditLog\AuditLogResource;
use App\Models\AuditLog;
use App\Models\Company;
use Mpdf\Mpdf;

class AuditReportController extends Controller
{
    public function index(AuditReportRequest $request)
    {
        $logs = $this->getAuditLogs($request)->paginate(10);

        return response()->json([
            'status' => true,
            'data' => AuditLogResource::collection($logs),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function pdf(AuditReportRequest $request)
    {
        $company = Company::firstOrFail();

        $logs = $this->getAuditLogs($request)->get();

        $html = view('reports.audit-report', compact(
            'company',
            'logs'
        ))->render();

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
        ]);

        $pdf->WriteHTML($html);

        return response(
            $pdf->Output('audit-report.pdf', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="audit-report.pdf"',
            ]
        );
    }

    private function getAuditLogs(AuditReportRequest $request)
    {
        $company = Company::firstOrFail();

        return AuditLog::query()
            ->where('company_id', $company->id)

            ->when($request->filled('username'), function ($query) use ($request) {
                $query->where('username', 'like', '%' . $request->username . '%');
            })

            ->when($request->filled('operation'), function ($query) use ($request) {
                $query->where('operation', 'like', '%' . $request->operation . '%');
            })

            ->when($request->filled('date'), function ($query) use ($request) {
                $query->whereDate('performed_at', $request->date);
            })

            ->latest('performed_at');
    }
}