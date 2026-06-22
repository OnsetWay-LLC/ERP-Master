<?php

namespace App\Http\Requests\PurchaseReturns;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.purchase_returns') === true;
    }

    public function rules(): array
    {
        return [
            
            'posting_time' => ['nullable', 'date_format:H:i'],
            'payment_due_date' => ['nullable', 'date'],

            'rejected_warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'target_warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],

            'tax_template_id' => ['nullable', 'integer', 'exists:tax_templates,id'],
            'fees_template_id' => ['nullable', 'integer', 'exists:fees_templates,id'],

            'apply_additional_discount_on' => ['nullable', 'in:grand_total,net_total'],
            'additional_discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_invoice_item_id' => ['required', 'integer', 'exists:purchase_invoice_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }
}