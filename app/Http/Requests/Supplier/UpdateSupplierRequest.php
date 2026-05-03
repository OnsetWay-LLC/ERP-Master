<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.suppliers');
    }

    public function rules(): array
    {
        return [
            'supplier_type' => ['sometimes', 'required', 'in:company,individual'],

            'supplier_name_ar' => ['sometimes', 'required'],
            'supplier_name_en' => ['sometimes', 'required'],

            'first_name' => ['nullable'],
            'last_name' => ['nullable'],

            'email' => ['nullable', 'email'],
            'mobile_number' => ['nullable'],

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