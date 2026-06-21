<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ItemWiseSalesRegisterReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.ItemWiseSalesRegisterReport') === true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'item_id' => ['nullable', 'integer', 'exists:items,id'],
        ];
    }
}