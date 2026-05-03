<?php

namespace App\Services\PurchaseOrder;

use App\Models\Company;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\MaterialRequestItem;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $companyId = Company::query()->value('id');

            $last = PurchaseOrder::latest('id')->first();
            $next = $last ? $last->id + 1 : 1;

            $po = PurchaseOrder::create([
                'company_id' => $companyId,
                'supplier_id' => $data['supplier_id'],
                'material_request_id' => $data['material_request_id'] ?? null,
                'order_number' => 'PO' . str_pad($next, 4, '0', STR_PAD_LEFT),
                'order_date' => now(),
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            $netTotal = 0;

            foreach ($data['items'] as $item) {

                $amount = $item['quantity'] * $item['rate'];
                $netTotal += $amount;

              $itemModel = Item::findOrFail($item['item_id']);

$poItem = $po->items()->create([
    'item_id' => $item['item_id'],
    'material_request_item_id' => $item['material_request_item_id'] ?? null,
    'target_warehouse_id' => $item['target_warehouse_id'],

    'item_code' => $itemModel->item_code,
    'barcode' => $itemModel->barcode,

    'quantity' => $item['quantity'],
    'rate' => $item['rate'],
    'amount' => $amount,
]);

                // 🔥 تحديث Material Request
                if (!empty($item['material_request_item_id'])) {

                    $mrItem = MaterialRequestItem::find($item['material_request_item_id']);

                    if ($mrItem) {
                        $mrItem->increment('ordered_qty', $item['quantity']);
                        $mrItem->update(['status' => 'ordered']);
                    }
                }
            }

            $po->update([
                'net_total' => $netTotal,
                'grand_total' => $netTotal,
            ]);

            return $po->load('items.item');
        });
    }

    public function submit(PurchaseOrder $po)
    {
        if ($po->status !== 'draft') {
            throw new \Exception('Only draft can be submitted');
        }

        $po->update(['status' => 'submitted']);

        return $po;
    }
}