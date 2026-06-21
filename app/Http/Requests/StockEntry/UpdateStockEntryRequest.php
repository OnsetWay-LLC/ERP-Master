<?php

namespace App\Http\Requests\StockEntry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.stock_entries') === true;
    }

    public function rules(): array
    {
        $companyId = 1;
        $entryType = $this->input('entry_type');

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
                Rule::requiredIf(fn () => in_array($entryType, [
                    'material_issue',
                    'material_transfer',
                ])),
                Rule::prohibitedIf(fn () => $entryType === 'material_receipt'),
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_group', true)
                    ->whereNull('deleted_at'),
            ],

            'items.*.target_warehouse_id' => [
                Rule::requiredIf(fn () => in_array($entryType, [
                    'material_receipt',
                    'material_transfer',
                ])),
                Rule::prohibitedIf(fn () => $entryType === 'material_issue'),
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_group', true)
                    ->whereNull('deleted_at'),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $entryType = $this->input('entry_type');
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                $sourceId = $item['source_warehouse_id'] ?? null;
                $targetId = $item['target_warehouse_id'] ?? null;

                if ($entryType === 'material_transfer') {
                    if ($sourceId && $targetId && (int) $sourceId === (int) $targetId) {
                        $validator->errors()->add(
                            "items.$index.target_warehouse_id",
                            'Source and target warehouses cannot be the same.'
                        );
                    }
                }
            }
        });
    }
}