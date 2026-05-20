<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.assets');
    }

    public function rules(): array
    {
        $companyId = 1;

        return [
            'asset_item_id' => [
                'required',
                'integer',
                Rule::exists('asset_items', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'location_id' => [
                'required',
                'integer',
                Rule::exists('asset_locations', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'asset_name_ar' => ['required', 'string', 'max:255'],
            'asset_name_en' => ['required', 'string', 'max:255'],

            'asset_type' => [
                'required',
                Rule::in([
                    'existing_asset',
                    'composite_asset',
                    'composite_component',
                ]),
            ],

            'purchase_date' => ['required', 'date'],

            'available_for_use_date' => [
                'required',
                'date',
                'after_or_equal:purchase_date',
            ],

            'net_purchase_amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'asset_quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'salvage_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'purchase_receipt_id' => [
                'required_if:asset_type,composite_component',
                'nullable',
                'integer',
                'exists:purchase_receipts,id',
            ],
        ];
    }
}