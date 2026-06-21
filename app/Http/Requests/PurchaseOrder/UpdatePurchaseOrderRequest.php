<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.purchase_orders') === true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'posting_date' => ['nullable', 'date'],
            'required_by_date' => ['nullable', 'date'],

            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'items' => ['nullable', 'array', 'min:1'],
            'items.*.material_request_id' => ['nullable', 'exists:material_requests,id'],
            'items.*.item_id' => ['nullable', 'exists:items,id'],
            'items.*.target_warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'items.*.required_by_date' => ['nullable', 'date'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],

            'tax_template_ids' => ['nullable', 'array'],
            'tax_template_ids.*' => ['exists:tax_templates,id'],

            'fees_template_ids' => ['nullable', 'array'],
            'fees_template_ids.*' => ['exists:fees_templates,id'],
        ];
    }
}