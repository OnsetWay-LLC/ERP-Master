<?php

namespace App\Http\Requests\AssetSale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetSaleRequest extends FormRequest
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
                'required',
                'integer',
                Rule::exists('assets', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'submitted')
                    ->whereNull('deleted_at'),
            ],

            'sell_qty' => ['required', 'numeric', 'min:0.01'],
            'posting_date' => ['required', 'date'],
            'posting_time' => ['nullable', 'date_format:H:i'],

            'warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'rate' => ['required', 'numeric', 'min:0.01'],

            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'sales_person_id' => [
                'required',
                'integer',
                Rule::exists('sales_people', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'payment_due_date' => ['nullable', 'date'],
            'posting_method' => ['nullable', Rule::in(['default', 'manual'])],

            'payment_mode' => [
                'required',
                Rule::in(['cash', 'bank', 'credit']),
            ],

            'payment_account_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'receivable_account_id' => [
                'required_if:payment_mode,credit',
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'cash_account_id' => [
                'required_if:payment_mode,cash',
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'bank_account_id' => [
                'required_if:payment_mode,bank',
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'sales_account_id' => [
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'cogs_account_id' => [
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'stock_account_id' => [
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'gain_account_id' => [
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'loss_account_id' => [
                'required',
                'integer',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('company_id', $companyId)
                    ->where('account_level', 'child')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_template_ids' => ['nullable', 'array'],
            'tax_template_ids.*' => ['integer'],
            'fees_template_ids' => ['nullable', 'array'],
            'fees_template_ids.*' => ['integer'],
        ];
    }
}