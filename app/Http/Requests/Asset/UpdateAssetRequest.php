<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
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

            'asset_name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'asset_name_en' => ['sometimes', 'required', 'string', 'max:255'],

            'asset_type' => [
                'sometimes',
                'required',
                Rule::in([
                    'existing_asset',
                    'composite_asset',
                    'composite_component',
                ]),
            ],

            'purchase_date' => ['sometimes', 'required', 'date'],

            'available_for_use_date' => [
                'sometimes',
                'required',
                'date',
            ],

            'net_purchase_amount' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01',
            ],

            'asset_quantity' => [
                'sometimes',
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
                'nullable',
                'integer',
                'exists:purchase_receipts,id',
            ],

            'status' => [
                'nullable',
                Rule::in(['active', 'disposed', 'inactive']),
            ],
        ];
    }
}