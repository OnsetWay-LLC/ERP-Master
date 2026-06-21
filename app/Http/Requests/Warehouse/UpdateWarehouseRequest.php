<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.warehouses') === true;
    }

    public function rules(): array
    {
        $warehouse = $this->route('warehouse');
        $companyId = $warehouse->company_id;

        $isGroup = $this->has('is_group')
            ? $this->boolean('is_group')
            : (bool) $warehouse->is_group;

        return [
            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name_ar')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($warehouse->id),
            ],

            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name_en')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at')
                    ->ignore($warehouse->id),
            ],

            'is_group' => ['sometimes', 'boolean'],

            'parent_id' => [
                'nullable',
                Rule::requiredIf(fn () => $isGroup === false),
                Rule::notIn([$warehouse->id]),
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_group', true)
                    ->whereNull('deleted_at'),
            ],

            'sales_person_id' => [
                'nullable',
                Rule::requiredIf(fn () => $isGroup === false),
                Rule::exists('sales_people', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'opening_balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }
}