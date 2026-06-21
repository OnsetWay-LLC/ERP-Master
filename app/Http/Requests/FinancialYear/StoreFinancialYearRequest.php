<?php

namespace App\Http\Requests\FinancialYear;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancialYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.account_closing');
    }

    public function rules(): array
    {
        $companyId = auth('api')->user()->company_id ?? 1;

        return [
            'year_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('financial_years', 'year_name')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'start_date' => [
                'required',
                'date',
            ],
        ];
    }
}