<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class SalesPersonPerformanceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.sales_person_performance_report');
    }

    public function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],

            'sales_person_id' => ['nullable', 'integer', 'exists:sales_people,id'],
            'item_group_id' => ['nullable', 'integer', 'exists:item_groups,id'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
        ];
    }
}