<?php

namespace App\Http\Requests\ChartOfAccount;

use App\Constants\AccountTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.chart_of_accounts');
    }

    public function rules(): array
    {
        $companyId = 1;
        $accountId = $this->route('id');

        return [
            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts', 'name_ar')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($accountId),
            ],

            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts', 'name_en')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($accountId),
            ],

            'account_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('chart_of_accounts', 'account_number')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($accountId),
            ],

            'account_type' => [
                'sometimes',
                'required',
                Rule::in(AccountTypes::values()),
            ],

            'is_active' => ['nullable', 'boolean'],
        ];
    }
}