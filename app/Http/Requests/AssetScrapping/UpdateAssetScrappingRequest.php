<?php

namespace App\Http\Requests\AssetScrapping;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetScrappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.assets.scrappings');
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

            'scrap_date' => [
                'sometimes',
                'required',
                'date',
                'before_or_equal:today',
            ],
        ];
    }
}