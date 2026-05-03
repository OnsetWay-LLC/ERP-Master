<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.suppliers');
    }

    public function rules(): array
    {
        return [
            'supplier_type' => ['required', 'in:company,individual'],

            'supplier_name_ar' => ['required', 'string', 'max:255'],
            'supplier_name_en' => ['required', 'string', 'max:255'],

            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],

            'email' => ['nullable', 'email'],
            'mobile_number' => ['nullable', 'string', 'max:30'],

            'address_line_1' => ['nullable'],
            'address_line_2' => ['nullable'],
            'zip_code' => ['nullable'],
            'city' => ['nullable'],
            'state_province' => ['nullable'],

            'country' => [
                'nullable',
                'string',
                'in:' . implode(',', array_keys(config('company.countries')))
            ],
        ];
    }
}