<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.purchase_orders');
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['sometimes', 'required', 'exists:suppliers,id'],
            'tax_template_id' => ['nullable', 'exists:tax_templates,id'],

            'order_date' => ['sometimes', 'required', 'date'],
            'required_by' => ['nullable', 'date'],

            'additional_discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.item_id' => ['required_with:items', 'exists:items,id'],
            'items.*.target_warehouse_id' => ['required_with:items', 'exists:warehouses,id'],
            'items.*.required_by' => ['nullable', 'date'],
            'items.*.quantity' => ['required_with:items', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }
}