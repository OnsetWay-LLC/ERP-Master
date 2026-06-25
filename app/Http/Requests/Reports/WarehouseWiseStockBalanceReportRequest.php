<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseWiseStockBalanceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.warehouse_wise_stock_balance_report') === true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'item_id' => ['nullable', 'integer', 'exists:items,id'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ];
    }
}