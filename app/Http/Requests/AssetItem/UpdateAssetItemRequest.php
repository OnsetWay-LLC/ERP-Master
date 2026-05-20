<?php

namespace App\Http\Requests\AssetItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.asset_items');
    }

    public function rules(): array
    {
        $companyId = 1;
        $id = $this->route('id');

        return [
            'item_code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('asset_items', 'item_code')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],

            'item_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'asset_category_id' => [
                'sometimes',
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