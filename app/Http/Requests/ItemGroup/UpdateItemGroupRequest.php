<?php

namespace App\Http\Requests\ItemGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.item_groups');
    }

    public function rules(): array
    {
        $itemGroup = $this->route('itemGroup');

        return [
            'warehouse_id' => ['sometimes', 'required', 'exists:warehouses,id'],

            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('item_groups', 'name_ar')
                    ->where(fn ($q) => $q->where('company_id', $itemGroup->company_id))
                    ->ignore($itemGroup->id)
                    ->whereNull('deleted_at'),
            ],

            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('item_groups', 'name_en')
                    ->where(fn ($q) => $q->where('company_id', $itemGroup->company_id))
                    ->ignore($itemGroup->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}