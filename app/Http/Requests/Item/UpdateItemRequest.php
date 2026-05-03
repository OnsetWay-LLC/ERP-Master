<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.items');
    }

    public function rules(): array
    {
        $item = $this->route('item');

        return [
            'item_group_id' => ['sometimes', 'required', 'exists:item_groups,id'],

            'item_code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('items', 'item_code')
                    ->where(fn ($q) => $q->where('company_id', $item->company_id))
                    ->ignore($item->id)
                    ->whereNull('deleted_at'),
            ],

            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],

            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'barcode')
                    ->where(fn ($q) => $q->where('company_id', $item->company_id))
                    ->ignore($item->id)
                    ->whereNull('deleted_at'),
            ],

            'selling_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'purchase_price' => ['sometimes', 'required', 'numeric', 'min:0'],

            'currency_code' => ['sometimes', 'required', 'string', 'max:10'],
            'status' => ['sometimes', 'required', 'in:active,inactive'],
        ];
    }
}