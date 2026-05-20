<?php

namespace App\Http\Requests\StockEntry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.stock_entries');
    }

    public function rules(): array
    {
        $companyId = 1;

        return [
            'entry_type' => [
                'required',
                Rule::in([
                    'material_receipt',
                    'material_issue',
                    'material_transfer',
                ]),
            ],

            'posting_date' => ['nullable', 'date'],
            'posting_time' => ['nullable', 'date_format:H:i:s'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.item_id' => [
                'required',
                'integer',
                Rule::exists('items', 'id'),
            ],

            'items.*.barcode' => ['nullable', 'string', 'max:255'],

            'items.*.source_warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'items.*.target_warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}