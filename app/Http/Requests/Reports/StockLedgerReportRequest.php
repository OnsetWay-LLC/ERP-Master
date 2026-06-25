<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class StockLedgerReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can( 'screen.stock_ledger_report',) === true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'item_id' => ['nullable', 'integer', 'exists:items,id'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'transaction_type' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}