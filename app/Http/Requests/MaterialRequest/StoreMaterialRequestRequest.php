<?php

namespace App\Http\Requests\MaterialRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaterialRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.material_requests') === true;
    }

    public function rules(): array
    {
        return [
            'required_by_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],

           

            'items.*.item_id' => [
                'required',
                Rule::exists('items', 'id')->whereNull('deleted_at'),
            ],

            'items.*.required_by_date' => [
                'required',
                'date',
            ],

            'items.*.required_qty' => [
                'required',
                'numeric',
                'min:1',
            ],

            'items.*.warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')
                    ->where('is_group', true)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}