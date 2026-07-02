<?php

namespace App\Http\Requests\AssetCategory;

use App\Constants\AccountTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.assets');
    }

    public function rules(): array
    {
        $companyId = 1;
        $id = $this->route('id');

        return [
            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('asset_categories', 'name_ar')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],

            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('asset_categories', 'name_en')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],

            'finance_book' => ['nullable', 'string', 'max:255'],

            'depreciation_method' => [
                'sometimes',
                'required',
                Rule::in([
                    'straight_line',
                    'double_declining_balance',
                    'written_down_value',
                    'manual',
                ]),
            ],

            'frequency_month' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],

            'total_depreciation_count' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'depreciation_posting_day' => [
                'nullable',
                'integer',
                'min:1',
                'max:31',
            ],

            'depreciation_rate' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:100',
            ],

            'fixed_asset_account_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_type', AccountTypes::FIXED_ASSET)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'accumulated_depreciation_account_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_type', AccountTypes::ACCUMULATED_DEPRECIATION)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'depreciation_expense_account_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_type', AccountTypes::DEPRECIATION)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'capital_work_in_progress_account_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_type', AccountTypes::CAPITAL_WORK_IN_PROGRESS)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}