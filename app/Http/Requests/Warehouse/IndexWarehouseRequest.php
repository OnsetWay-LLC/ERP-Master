<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class IndexWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.warehouses') === true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'is_group' => ['nullable', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'sales_person_id' => ['nullable', 'integer', 'exists:sales_people,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'trashed' => ['nullable', 'in:with,only'],
        ];
    }
}