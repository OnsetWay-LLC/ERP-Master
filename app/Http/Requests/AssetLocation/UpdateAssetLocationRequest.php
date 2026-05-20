<?php

namespace App\Http\Requests\AssetLocation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.asset_locations');
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
                Rule::unique('asset_locations', 'name_ar')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],

            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('asset_locations', 'name_en')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($id),
            ],

            'is_active' => ['nullable', 'boolean'],
        ];
    }
}