<?php

namespace App\Http\Requests\AssetCapitalization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetCapitalizationRequest extends FormRequest
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
    'required',
    'integer',
    Rule::exists('assets', 'id')
        ->where('company_id', $companyId)
        ->where('asset_type', 'composite_asset')
        ->where('status', 'submitted')
        ->whereNull('deleted_at'),
],

            'posting_date' => ['required', 'date'],
            'posting_time' => ['required', 'date_format:H:i'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.asset_id' => [
                'required',
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