<?php

namespace App\Http\Requests\StockEntry;

use Illuminate\Foundation\Http\FormRequest;

class StockBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
      return auth()->user()->can('screen.stock_entries');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],

            'warehouse_type' => [
                'nullable',
                'in:parent,child'
            ],

            'warehouse_id' => [
                'nullable',
                'exists:warehouses,id'
            ],

            'item_id' => [
                'nullable',
                'exists:items,id'
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100'
            ],
        ];
    }
}