<?php

namespace App\Http\Resources\PurchaseOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'order_number' => $this->order_number,
            'order_date' => $this->order_date,
            'required_by' => $this->required_by,
            'status' => $this->status,

            'supplier' => [
                'id' => $this->supplier?->id,
                'supplier_name_ar' => $this->supplier?->supplier_name_ar,
                'supplier_name_en' => $this->supplier?->supplier_name_en,
            ],

            'tax_template' => [
                'id' => $this->taxTemplate?->id,
                'title' => $this->taxTemplate?->title,
            ],

            'items' => $this->items->map(function ($row) {
                return [
                    'id' => $row->id,
                    'item_id' => $row->item_id,
                    'item_code' => $row->item_code,
                    'barcode' => $row->barcode,
                    'item_name_ar' => $row->item?->name_ar,
                    'item_name_en' => $row->item?->name_en,

                    'target_warehouse' => [
                        'id' => $row->targetWarehouse?->id,
                        'name_ar' => $row->targetWarehouse?->name_ar,
                        'name_en' => $row->targetWarehouse?->name_en,
                    ],

                    'required_by' => $row->required_by,
                    'quantity' => $row->quantity,
                    'rate' => $row->rate,
                    'amount' => $row->amount,
                ];
            }),

            'taxes' => $this->taxes->map(function ($tax) {
                return [
                    'id' => $tax->id,
                    'type' => $tax->type,
                    'account_id' => $tax->account_id,
                    'account_name' => $tax->account?->name,
                    'tax_rate' => $tax->tax_rate,
                    'amount' => $tax->amount,
                ];
            }),

            'net_total' => $this->net_total,
            'tax_total' => $this->tax_total,
            'additional_discount_percentage' => $this->additional_discount_percentage,
            'additional_discount_amount' => $this->additional_discount_amount,
            'grand_total' => $this->grand_total,

            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'username' => $this->creator?->username,
            ],

            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}