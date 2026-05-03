<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.purchase_orders');
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'material_request_id' => ['nullable', 'exists:material_requests,id'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.target_warehouse_id' => ['required', 'exists:warehouses,id'],

            'items.*.material_request_item_id' => ['nullable', 'exists:material_request_items,id'],
        ];
    }
}