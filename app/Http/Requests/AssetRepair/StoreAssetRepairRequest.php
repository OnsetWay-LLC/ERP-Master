<?php

namespace App\Http\Requests\AssetRepair;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRepairRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.asset_repairs');
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

            'repair_status' => [
                'required',
                Rule::in(['pending', 'completed', 'cancelled']),
            ],

            'failure_date' => ['required', 'date'],

            'error_description' => ['nullable', 'string'],
            'actions_performed' => ['nullable', 'string'],

           
            'items.*.purchase_invoice_id' => [
                'required_with:items',
                'integer',
                'distinct',
                Rule::exists('purchase_invoices', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'submitted'),
            ],

          
        ];
    }
}