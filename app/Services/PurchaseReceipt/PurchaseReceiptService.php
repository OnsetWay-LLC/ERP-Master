<?php
namespace App\Services\PurchaseReceipt;

use App\Models\Company;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialRequestItem;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $companyId = Company::query()->value('id');

            $last = PurchaseReceipt::latest('id')->first();
            $next = $last ? $last->id + 1 : 1;

            $receipt = PurchaseReceipt::create([
                'company_id' => $companyId,
                'purchase_order_id' => $data['purchase_order_id'],
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'receipt_number' => 'PR' . str_pad($next, 4, '0', STR_PAD_LEFT),
                'receipt_date' => now(),
                'status' => 'submitted',
                'created_by' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {

              $receipt->items()->create([
    'purchase_order_item_id' => $item['purchase_order_item_id'],
    'item_id' => $item['item_id'],
    'warehouse_id' => $data['warehouse_id'],
    'quantity' => $item['quantity'],
]);
// تحديث PO
$poItem = PurchaseOrderItem::findOrFail($item['purchase_order_item_id']);
$poItem->increment('received_qty', $item['quantity']);

// تحديث Material Request
if ($poItem->material_request_item_id) {
    $mrItem = MaterialRequestItem::findOrFail($poItem->material_request_item_id);

    $mrItem->increment('received_qty', $item['quantity']);
    $mrItem->refresh();

    if ($mrItem->received_qty >= $mrItem->required_qty) {
        $mrItem->update(['status' => 'received']);
    } elseif ($mrItem->received_qty > 0) {
        $mrItem->update(['status' => 'partially_received']);
    }
}
                

                // 🔥 Stock Ledger
                $lastBalance = StockLedger::where('item_id', $item['item_id'])
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->latest('id')
                    ->value('balance_qty') ?? 0;

                $newBalance = $lastBalance + $item['quantity'];

                StockLedger::create([
                    'company_id' => $companyId,
                    'item_id' => $item['item_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'entry_date' => now(),
                    'reference_type' => 'purchase_receipt',
                    'reference_id' => $receipt->id,
                    'quantity_in' => $item['quantity'],
                    'quantity_out' => 0,
                    'balance_qty' => $newBalance,
                ]);
            }

            return $receipt->load('items');
        });
    }
  public function update(PurchaseReceipt $receipt, array $data)
{
    return DB::transaction(function () use ($receipt, $data) {
        $receipt->update([
            'purchase_order_id' => $data['purchase_order_id'],
            'supplier_id' => $data['supplier_id'],
            'warehouse_id' => $data['warehouse_id'],
        ]);

        $receipt->items()->delete();

        foreach ($data['items'] as $item) {
            $receipt->items()->create([
                'purchase_order_item_id' => $item['purchase_order_item_id'],
                'item_id' => $item['item_id'],
                'warehouse_id' => $data['warehouse_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return $receipt->fresh()->load('items');
    });
}
}