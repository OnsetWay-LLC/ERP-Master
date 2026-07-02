<?php

namespace App\Http\Requests\AssetCapitalization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetCapitalizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.asset-capitalizations');
    }

    public function rules(): array
    {
        $companyId = auth('api')->user()->company_id ?? 1;

        return [
            'target_asset_id' => [
    'sometimes',
    'nullable',
    'integer',
    Rule::exists('assets', 'id')
        ->where('company_id', $companyId)
        ->where('asset_type', 'composite_asset')
        ->where('status', 'submitted')
        ->whereNull('deleted_at'),
],

            'posting_date' => ['sometimes', 'required', 'date'],
            'posting_time' => ['sometimes', 'required', 'date_format:H:i'],

            'items' => ['sometimes', 'required', 'array', 'min:1'],

            'items.*.asset_id' => [
                'required_with:items',
                'integer',
                'distinct',
                Rule::exists('assets', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'submitted')
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}