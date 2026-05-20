<?php

namespace App\Http\Requests\AssetCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.assets');
    }

    public function rules(): array
    {
        $companyId = 1;

        return [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('asset_categories', 'name_ar')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('asset_categories', 'name_en')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'finance_book' => ['nullable', 'string', 'max:255'],

            'depreciation_method' => [
                'required',
                Rule::in([
                    'straight_line',
                    'double_declining_balance',
                    'written_down_value',
                    'manual',
                ]),
            ],

            'frequency_month' => [
                'required_unless:depreciation_method,manual',
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],

            'total_depreciation_count' => [
                'required_unless:depreciation_method,manual',
                'nullable',
                'integer',
                'min:1',
            ],

            'depreciation_posting_day' => [
                'required_unless:depreciation_method,manual',
                'nullable',
                'integer',
                'min:1',
                'max:31',
            ],

            'depreciation_rate' => [
                'required_if:depreciation_method,written_down_value',
                'nullable',
                'numeric',
                'min:0.01',
                'max:100',
            ],

            'fixed_asset_account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'accumulated_depreciation_account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'depreciation_expense_account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
'capital_work_in_progress_account_id' => [
    'nullable',
    'integer',
    'exists:chart_of_accounts,id',
],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}