<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.customers');
    }

    public function rules(): array
    {
        return [
            'customer_type' => ['sometimes', 'required', 'in:company,individual'],

            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],

            'email' => ['nullable', 'email', 'max:255'],
            'mobile_number' => ['nullable', 'string', 'max:30'],

            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:30'],
            'state_province' => ['nullable', 'string', 'max:100'],

            'country' => [
                'nullable',
                'string',
                'in:' . implode(',', array_keys(config('company.countries'))),
            ],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to access the customers screen.');
    }
}