<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class SupplierLedgerReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.supplier_ledger_reports') === true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ];
    }
}