<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrossProfitReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check()
            && auth('api')->user()->can('screen.gross_profit_report');
    }

    public function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],

            'filter_by' => [
                'required',
                Rule::in([
                    'sales_invoice',
                    'item_code',
                    'item_group',
                    'warehouse',
                    'customer',
                    'sales_person',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'from_date.required' => 'From date is required.',
            'to_date.required' => 'To date is required.',
            'to_date.after_or_equal' => 'To date must be after or equal to from date.',
            'filter_by.required' => 'Filter type is required.',
            'filter_by.in' => 'Invalid filter type.',
        ];
    }
}