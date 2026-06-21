<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ItemWisePurchaseRegisterReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.ItemWisePurchaseRegisterReport');
    }

    public function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'item_id' => ['nullable', 'exists:items,id'],
        ];
    }
}