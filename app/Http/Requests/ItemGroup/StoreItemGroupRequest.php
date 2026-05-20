<?php

namespace App\Http\Requests\ItemGroup;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.item_groups');
    }

    public function rules(): array
    {
        $companyId = Company::query()->value('id');

        return [

            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('item_groups', 'name_ar')
                    ->where(fn ($q) => $q->where('company_id', $companyId))
                    ->whereNull('deleted_at'),
            ],

            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('item_groups', 'name_en')
                    ->where(fn ($q) => $q->where('company_id', $companyId))
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}