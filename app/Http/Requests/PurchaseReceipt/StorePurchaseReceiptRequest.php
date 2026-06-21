<?php

namespace App\Http\Requests\PurchaseReceipt;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.purchase_receipts') === true;
    }

    public function rules(): array
    {
        $companyId = 1;

        return [
            'purchase_order_id' => [
                'required',
                Rule::exists('purchase_orders', 'id')
                    ->where('company_id', $companyId)
                    ->where('status', 'confirmed')
                    ->whereNull('deleted_at'),
            ],

            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->whereNull('deleted_at'),
            ],

            'posting_date' => ['nullable', 'date'],
            'posting_time' => ['nullable', 'date_format:H:i:s'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.purchase_order_item_id' => [
                'required',
                Rule::exists('purchase_order_items', 'id'),
            ],

            'items.*.item_id' => [
                'required',
                Rule::exists('items', 'id')->whereNull('deleted_at'),
            ],

            'items.*.accepted_warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_group', true)
                    ->whereNull('deleted_at'),
            ],

            'items.*.rejected_warehouse_id' => [
                'nullable',
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_group', true)
                    ->whereNull('deleted_at'),
            ],

            'items.*.accepted_qty' => ['required', 'numeric', 'min:0'],
            'items.*.rejected_qty' => ['nullable', 'numeric', 'min:0'],
            
            'tax_template_ids' => ['nullable', 'array'],
            'tax_template_ids.*' => [
                Rule::exists('tax_templates', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'fees_template_ids' => ['nullable', 'array'],
            'fees_template_ids.*' => [
                Rule::exists('fees_templates', 'id')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],

            'additional_discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'additional_discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $purchaseOrderId = $this->input('purchase_order_id');
            $supplierId = $this->input('supplier_id');

            $purchaseOrder = PurchaseOrder::query()->find($purchaseOrderId);

            if ($purchaseOrder && (int) $purchaseOrder->supplier_id !== (int) $supplierId) {
                $validator->errors()->add(
                    'supplier_id',
                    'Supplier does not match purchase order supplier.'
                );
            }

            foreach ($this->input('items', []) as $index => $item) {
                $acceptedQty = (float) ($item['accepted_qty'] ?? 0);
                $rejectedQty = (float) ($item['rejected_qty'] ?? 0);
                $totalQty = $acceptedQty + $rejectedQty;

                if ($totalQty <= 0) {
                    $validator->errors()->add(
                        "items.$index.accepted_qty",
                        'Accepted quantity or rejected quantity must be greater than zero.'
                    );
                    continue;
                }

                $poItem = PurchaseOrderItem::query()
                    ->where('id', $item['purchase_order_item_id'] ?? null)
                    ->where('purchase_order_id', $purchaseOrderId)
                    ->first();

                if (! $poItem) {
                    $validator->errors()->add(
                        "items.$index.purchase_order_item_id",
                        'Purchase order item does not belong to selected purchase order.'
                    );
                    continue;
                }

                if ((int) $poItem->item_id !== (int) ($item['item_id'] ?? 0)) {
                    $validator->errors()->add(
                        "items.$index.item_id",
                        'Item does not match purchase order item.'
                    );
                    continue;
                }

                $remainingQty = (float) $poItem->quantity - (float) $poItem->received_qty;

                if ($totalQty > $remainingQty) {
                    $validator->errors()->add(
                        "items.$index.accepted_qty",
                        'Received quantity cannot exceed remaining purchase order quantity.'
                    );
                }
            }
        });
    }
}