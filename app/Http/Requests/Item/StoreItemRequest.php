<?php

namespace App\Http\Requests\Item;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.items');
    }

    public function rules(): array
    {
        $companyId = Company::query()->value('id');

        return [
            'item_group_id' => ['required', 'exists:item_groups,id'],

            'item_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('items', 'item_code')
                    ->where(fn ($q) => $q->where('company_id', $companyId))
                    ->whereNull('deleted_at'),
            ],

            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],

            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'barcode')
                    ->where(fn ($q) => $q->where('company_id', $companyId))
                    ->whereNull('deleted_at'),
            ],

            'selling_price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],

            'currency_code' => ['required', 'string', 'max:10'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}