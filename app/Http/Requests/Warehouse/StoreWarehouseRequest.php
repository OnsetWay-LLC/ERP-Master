<?php

namespace App\Http\Requests\Warehouse;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.warehouses') === true;
    }

    public function rules(): array
    {
        $companyId = Company::query()->value('id');

        return [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name_ar')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name_en')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'is_group' => ['required', 'boolean'],

            'parent_id' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('is_group') === false),
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_group', true)
                    ->whereNull('deleted_at'),
            ],

            'sales_person_id' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('is_group') === false),
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