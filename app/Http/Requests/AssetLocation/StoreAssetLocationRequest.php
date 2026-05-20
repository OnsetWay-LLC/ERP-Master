<?php

namespace App\Http\Requests\AssetLocation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.asset_locations');
    }

    public function rules(): array
    {
        $companyId = 1;

        return [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('asset_locations', 'name_ar')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('asset_locations', 'name_en')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'is_active' => ['nullable', 'boolean'],
        ];
    }
}