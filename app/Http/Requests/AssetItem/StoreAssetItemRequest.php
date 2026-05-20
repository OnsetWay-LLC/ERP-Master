<?php

namespace App\Http\Requests\AssetItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.asset_items');
    }

    public function rules(): array
    {
        $companyId = 1;

        return [
            'item_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asset_items', 'item_code')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'item_name' => [
                'required',
                'string',
                'max:255',
            ],

            'asset_category_id' => [
                'required',
                'integer',
                Rule::exists('asset_categories', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'is_active' => ['nullable', 'boolean'],
        ];
    }
}