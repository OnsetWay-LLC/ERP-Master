<?php

namespace App\Http\Requests\ChartOfAccount;

use App\Constants\AccountTypes;
use App\Constants\ChartOfAccountCategories;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.chart_of_accounts');
    }

    public function rules(): array
    {
        $companyId = 1;

        return [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts', 'name_ar')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('chart_of_accounts', 'name_en')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'account_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('chart_of_accounts', 'account_number')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'account_type' => [
                'required',
                Rule::in(AccountTypes::values()),
            ],

            'account_level' => [
                'required',
                Rule::in(['parent', 'child']),
            ],

            'parent_account_id' => [
                'required_if:account_level,child',
                'nullable',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'parent')
                    ->whereNull('deleted_at'),
            ],

            'root_category' => [
                'required_if:account_type,other',
                'nullable',
                Rule::in(ChartOfAccountCategories::roots()),
            ],

            'sub_category' => [
                'required',
                Rule::in(ChartOfAccountCategories::subCategories()),
            ],

            'is_active' => ['nullable', 'boolean'],
        ];
    }
}