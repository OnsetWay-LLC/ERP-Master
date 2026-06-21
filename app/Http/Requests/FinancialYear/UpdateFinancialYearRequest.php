<?php

namespace App\Http\Requests\FinancialYear;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancialYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.account_closing');
    }

    public function rules(): array
    {
        $companyId = auth('api')->user()->company_id ?? 1;
        $financialYearId = $this->route('financialYear')?->id;

        return [
            'year_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('financial_years', 'year_name')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($financialYearId),
            ],

            'start_date' => [
                'sometimes',
                'required',
                'date',
            ],
        ];
    }
}