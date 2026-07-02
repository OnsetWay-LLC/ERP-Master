<?php

namespace App\Services\DeliveryNote;

use App\Models\Company;
use App\Models\DeliveryNote;
use App\Models\PickList;
use App\Models\StockEntry;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeliveryNoteService
{
    public function getAll()
    {
        $companyId = Company::query()->firstOrFail()->id;

        return DeliveryNote::query()
            ->with(['salesOrder', 'pickList', 'customer', 'items.warehouse'])
            ->where('company_id', $companyId)
            ->latest()
            ->get();
    }

    public function show(DeliveryNote $deliveryNote): DeliveryNote
    {
        return $deliveryNote->load([
            'salesOrder',
            'pickList',
            'customer',
            'items.item',
            'items.warehouse',
            'taxes.account',
            'fees.account',
        ]);
    }

    public function createFromPickList(PickList $pickList): DeliveryNote
    {
        if ($pickList->status !== 'open') {
            throw new RuntimeException('Only open pick lists can create delivery notes.');
        }

        return DB::transaction(function () use ($pickList) {
            $pickList->load([
                'salesOrder.items',
                'salesOrder.taxes',
                'salesOrder.fees',
                'items',
            ]);

            if ($pickList->deliveryNote()->exists()) {
                throw new RuntimeException('Delivery note already exists for this pick list.');
            }

            $salesOrder = $pickList->salesOrder;
$pendingApproval = $salesOrder->discountApprovalRequests()
    ->whereIn('status', [
        'pending_department_manager_approval',
        'pending_department_manager_decision',
        'pending_cfo_approval',
    ])
    ->exists();

if ($pendingApproval) {
    throw new RuntimeException(
        'Cannot create delivery note while discount approval is pending.'
    );
}

$rejectedApproval = $salesOrder->discountApprovalRequests()
    ->where('status', 'rejected')
    ->exists();

if ($rejectedApproval) {
    throw new RuntimeException(
        'Cannot create delivery note because the discount request was rejected.'
    );
}
            if ($salesOrder->status !== 'delivery_and_to_bill') {
                throw new RuntimeException('Sales order is not ready for delivery.');
            }

            $deliveryNote = DeliveryNote::query()->create([
                'company_id' => $pickList->company_id,
                'sales_order_id' => $pickList->sales_order_id,
                'pick_list_id' => $pickList->id,
                'customer_id' => $pickList->customer_id,
                'delivery_note_number' => $this->generateDeliveryNoteNumber($pickList->company_id),
                'posting_date' => now()->toDateString(),
                'posting_time' => now()->format('H:i'),
                'total_qty' => $salesOrder->items->sum('quantity'),
                'net_total' => $salesOrder->net_total,
                'tax_total' => $salesOrder->tax_total,
                'fees_total' => $salesOrder->fees_total,
                'discount_percentage' => $salesOrder->discount_percentage,
                'discount_amount' => $salesOrder->discount_amount,
                'grand_total' => $salesOrder->grand_total,
                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ]);

            foreach ($pickList->items as $pickItem) {
                $salesOrderItem = $salesOrder->items
                    ->where('id', $pickItem->sales_order_item_id)
                    ->first();

                $deliveryNote->items()->create([
                    'sales_order_item_id' => $pickItem->sales_order_item_id,
                    'pick_list_item_id' => $pickItem->id,
                    'item_id' => $pickItem->item_id,
                    'warehouse_id' => $pickItem->warehouse_id,
                    'item_code' => $pickItem->item_code,
                    'item_name_ar' => $pickItem->item_name_ar,
                    'item_name_en' => $pickItem->item_name_en,
                    'quantity' => $pickItem->picked_quantity,
                    'rate' => $salesOrderItem?->rate ?? 0,
                    'amount' => $pickItem->picked_quantity * ($salesOrderItem?->rate ?? 0),
                ]);
            }

            foreach ($salesOrder->taxes as $tax) {
                $deliveryNote->taxes()->create([
                    'tax_template_id' => $tax->tax_template_id,
                    'tax_template_line_id' => $tax->tax_template_line_id,
                    'title' => $tax->title,
                    'type' => $tax->type,
                    'account_id' => $tax->account_id,
                    'tax_rate' => $tax->tax_rate,
                    'amount' => $tax->amount,
                ]);
            }

            foreach ($salesOrder->fees as $fee) {
                $deliveryNote->fees()->create([
                    'fees_template_id' => $fee->fees_template_id,
                    'title' => $fee->title,
                    'type' => $fee->type,
                    'account_id' => $fee->account_id,
                    'fees_rate' => $fee->fees_rate,
                    'amount' => $fee->amount,
                ]);
            }

            return $deliveryNote->fresh()->load([
                'salesOrder',
                'pickList',
                'customer',
                'items.warehouse',
                'taxes',
                'fees',
            ]);
        });
    }

    public function submit(DeliveryNote $deliveryNote): DeliveryNote
{
    if ($deliveryNote->status !== 'draft') {
        throw new RuntimeException('Only draft delivery notes can be submitted.');
    }

    return DB::transaction(function () use ($deliveryNote) {
        $deliveryNote->load([
            'items',
            'pickList',
            'salesOrder',
        ]);

        foreach ($deliveryNote->items as $item) {
            $stock = $this->getStockForUpdate(
                $deliveryNote->company_id,
                $item->item_id,
                $item->warehouse_id
            );

            $qty = (float) $item->quantity;

            if ((float) $stock->reserved_quantity < $qty) {
                throw new RuntimeException(
                    'Reserved quantity is not enough for item: ' . $item->item_name_en
                );
            }

            if ((float) $stock->quantity < $qty) {
                throw new RuntimeException(
                    'Stock quantity is not enough for item: ' . $item->item_name_en
                );
            }

            $newQuantity = (float) $stock->quantity - $qty;
            $newReservedQuantity = (float) $stock->reserved_quantity - $qty;

            if ($newQuantity < 0) {
                throw new RuntimeException(
                    'Stock quantity cannot be negative for item: ' . $item->item_name_en
                );
            }

            if ($newReservedQuantity < 0) {
                throw new RuntimeException(
                    'Reserved quantity cannot be negative for item: ' . $item->item_name_en
                );
            }

            $stock->update([
                'quantity' => $newQuantity,
                'reserved_quantity' => $newReservedQuantity,
                'stock_value' => $newQuantity * (float) $stock->average_rate,
            ]);
        }

        $this->createMaterialIssue($deliveryNote);

        $deliveryNote->update([
            'status' => 'to_bill',
        ]);

        $deliveryNote->pickList->update([
            'status' => 'completed',
        ]);

        $deliveryNote->salesOrder->update([
            'status' => 'to_bill',
        ]);

        return $deliveryNote->fresh()->load([
            'salesOrder',
            'pickList',
            'customer',
            'items.warehouse',
            'taxes',
            'fees',
        ]);
    });
}

    public function cancel(DeliveryNote $deliveryNote): DeliveryNote
    {
        if ($deliveryNote->status !== 'draft') {
            throw new RuntimeException('Only draft delivery notes can be cancelled.');
        }

        $deliveryNote->update([
            'status' => 'cancelled',
        ]);

        return $deliveryNote->fresh()->load([
            'salesOrder',
            'pickList',
            'customer',
            'items.warehouse',
        ]);
    }

    private function createMaterialIssue(DeliveryNote $deliveryNote): void
    {
        $stockEntry = StockEntry::query()->create([
            'company_id' => $deliveryNote->company_id,
            'series' => $this->generateStockEntrySeries($deliveryNote->company_id),
            'entry_type' => 'material_issue',
            'posting_date' => $deliveryNote->posting_date,
            'posting_time' => $deliveryNote->posting_time,
            'total_incoming_value' => 0,
            'total_outgoing_value' => 0,
            'value_difference' => 0,
            'status' => 'submitted',
            'created_by' => auth('api')->id(),
        ]);

        $totalOutgoing = 0;

        foreach ($deliveryNote->items as $item) {
            $stock = WarehouseStock::query()
                ->where('company_id', $deliveryNote->company_id)
                ->where('item_id', $item->item_id)
                ->where('warehouse_id', $item->warehouse_id)
                ->first();

            $basicRate = $stock?->average_rate ?? 0;
            $outgoingValue = $item->quantity * $basicRate;
            $totalOutgoing += $outgoingValue;

            $stockEntry->items()->create([
                'item_id' => $item->item_id,
                'barcode' => $item->item?->barcode ?? null,
                'source_warehouse_id' => $item->warehouse_id,
                'target_warehouse_id' => null,
                'quantity' => $item->quantity,
                'basic_rate' => $basicRate,
                'incoming_value' => 0,
                'outgoing_value' => $outgoingValue,
                'value_difference' => 0 - $outgoingValue,
            ]);
        }

        $stockEntry->update([
            'total_outgoing_value' => $totalOutgoing,
            'value_difference' => 0 - $totalOutgoing,
        ]);
    }

    private function getStockForUpdate(int $companyId, int $itemId, int $warehouseId): WarehouseStock
    {
        $stock = WarehouseStock::query()
            ->where('company_id', $companyId)
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            throw new RuntimeException('Warehouse stock record not found.');
        }

        return $stock;
    }

    private function generateDeliveryNoteNumber(int $companyId): string
    {
        $count = DeliveryNote::query()
            ->where('company_id', $companyId)
            ->count() + 1;

        return 'DN-' . now()->format('Y') . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    private function generateStockEntrySeries(int $companyId): string
    {
        $count = StockEntry::query()
            ->where('company_id', $companyId)
            ->count() + 1;

        return 'STE-' . now()->format('Y') . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}