<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class IndexCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.customers');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['nullable', 'in:company,individual'],
            'country' => [
                'nullable',
                'string',
                'in:' . implode(',', array_keys(config('company.countries'))),
            ],
            'city' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'trashed' => ['nullable', 'in:with,only'],
        ];
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to access the customers screen.');
    }
}