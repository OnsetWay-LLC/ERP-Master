<?php

namespace App\Http\Requests\AssetSale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.asset_Sale');
    }

    public function rules(): array
    {
        $companyId = auth('api')->user()->company_id ?? 1;

        return [
            'asset_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('assets', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'submitted')
                    ->whereNull('deleted_at'),
            ],

            'sell_qty' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'posting_date' => ['sometimes', 'required', 'date'],
            'posting_time' => ['nullable', 'date_format:H:i'],

            'warehouse_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'rate' => ['sometimes', 'required', 'numeric', 'min:0.01'],

            'payment_mode' => [
                'sometimes',
                'required',
                Rule::in(['cash', 'bank', 'credit']),
            ],

            'receivable_account_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'cash_account_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'bank_account_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'gain_account_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'loss_account_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}