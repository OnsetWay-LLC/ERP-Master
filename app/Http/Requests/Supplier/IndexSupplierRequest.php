<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class IndexSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.suppliers');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'supplier_type' => ['nullable', 'in:company,individual'],
            'country' => ['nullable'],
            'city' => ['nullable'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'trashed' => ['nullable', 'in:with,only'],
        ];
    }
}