<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.assets');
    }

    public function rules(): array
    {
        $companyId = auth('api')->user()->company_id ?? 1;

        return [
            'asset_item_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('asset_items', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'location_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('asset_locations', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'asset_type' => [
                'sometimes',
                'required',
                Rule::in(['existing_asset', 'composite_asset', 'composite_component']),
            ],

          'purchase_invoice_id' => [
    'nullable',
    'integer',
    'required_if:asset_type,composite_component',
    'prohibited_if:asset_type,existing_asset',
    'prohibited_if:asset_type,composite_asset',
    Rule::exists('purchase_invoices', 'id')
        ->where('company_id', $companyId),
],

           'purchase_date' => [
    'sometimes',
    'nullable',
    'date',
    'prohibited_if:asset_type,composite_component',
],
            'available_for_use_date' => ['sometimes', 'required', 'date'],

            'net_purchase_amount' => [
    'sometimes',
    'nullable',
    'numeric',
    'min:0.01',
    'prohibited_if:asset_type,composite_asset',
    'prohibited_if:asset_type,composite_component',
],
            'asset_quantity' => ['sometimes', 'required', 'integer', 'min:1'],
         

            'opening_accumulated_depreciation' => ['nullable', 'numeric', 'min:0'],
            'opening_number_of_booked_depreciations' => ['nullable', 'integer', 'min:0'],
        ];
    }
}