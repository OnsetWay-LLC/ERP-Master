<?php

namespace App\Services\PurchaseOrder;

use App\Models\Company;
use App\Models\FeesTemplate;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\TaxTemplate;
use App\Models\Warehouse;
use App\Models\MaterialRequestItem;
use App\Models\MaterialRequest;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrderService
{
    public function create(array $data): PurchaseOrder
    {
        app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        Company::firstOrFail()->id,
        $data['posting_date'],
        'create'
    );
        return DB::transaction(function () use ($data) {
            $company = Company::firstOrFail();

            $totals = $this->calculateTotals($company->id, $data);

            $purchaseOrder = PurchaseOrder::create([
                'company_id' => $company->id,
                'supplier_id' => $data['supplier_id'],
                'series' => $this->generateSeries($company->id),
                'posting_date' => $data['posting_date'],
                'required_by_date' => $data['required_by_date'] ?? null,
                'total_quantity' => $totals['total_quantity'],
                'net_total' => $totals['net_total'],
                'tax_total' => $totals['tax_total'],
                'fees_total' => $totals['fees_total'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],
                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ]);

            $this->saveItems($purchaseOrder, $data['items']);

$this->updateMaterialRequestOrderedQty($data['items']);

$this->saveTaxes($purchaseOrder, $company->id, $data['tax_template_ids'] ?? [], $totals['net_total']);
$this->saveFees($purchaseOrder, $company->id, $data['fees_template_ids'] ?? [], $totals['net_total']);

            return $purchaseOrder->fresh()->load([
                'supplier',
                'items.item',
                'items.targetWarehouse',
                'taxes.account',
                'fees.account',
            ]);
        });
    }
private function updateMaterialRequestOrderedQty(array $items): void
{
    foreach ($items as $row) {
        if (empty($row['material_request_id'])) {
            continue;
        }

        $materialRequestItem = MaterialRequestItem::query()
            ->where('material_request_id', $row['material_request_id'])
            ->where('item_id', $row['item_id'])
            ->where('warehouse_id', $row['target_warehouse_id'])
            ->lockForUpdate()
            ->first();

        if (! $materialRequestItem) {
            throw new RuntimeException(
                'Material request item not found for selected item and warehouse.'
            );
        }

        if (in_array($materialRequestItem->status, ['ordered', 'completed', 'cancelled'])) {
            throw new RuntimeException(
                'This material request item cannot be ordered again.'
            );
        }

        $newOrderedQty =
            (float) $materialRequestItem->ordered_qty
            + (float) $row['quantity'];

        if ($newOrderedQty > (float) $materialRequestItem->required_qty) {
            throw new RuntimeException(
                'Ordered quantity cannot exceed required quantity.'
            );
        }

        $newStatus = 'pending';

        if ($newOrderedQty > 0 && $newOrderedQty < (float) $materialRequestItem->required_qty) {
            $newStatus = 'partially_ordered';
        }

        if ($newOrderedQty >= (float) $materialRequestItem->required_qty) {
            $newStatus = 'ordered';
        }

        $materialRequestItem->update([
            'ordered_qty' => $newOrderedQty,
            'status' => $newStatus,
        ]);

        $this->refreshMaterialRequestStatus($materialRequestItem->material_request_id);
    }
}
private function refreshMaterialRequestStatus(int $materialRequestId): void
{
    $materialRequest = MaterialRequest::with('items')
        ->lockForUpdate()
        ->findOrFail($materialRequestId);

    $items = $materialRequest->items;

    if ($items->every(fn ($item) => $item->status === 'ordered')) {
        $materialRequest->update([
            'status' => 'ordered',
        ]);

        return;
    }

    if ($items->contains(fn ($item) => $item->status === 'partially_ordered')) {
        $materialRequest->update([
            'status' => 'partially_ordered',
        ]);

        return;
    }

    $materialRequest->update([
        'status' => 'sent_to_purchase_order',
    ]);
}
    public function update(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        if ($purchaseOrder->status !== 'draft') {
            throw new RuntimeException('Only draft purchase orders can be updated.');
        }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $purchaseOrder->company_id,
        $data['posting_date'] ?? $purchaseOrder->posting_date,
        'update'
    );
        return DB::transaction(function () use ($purchaseOrder, $data) {
            $companyId = $purchaseOrder->company_id;

            $totals = $this->calculateTotals($companyId, $data);

            $purchaseOrder->update([
                'supplier_id' => $data['supplier_id'],
                'posting_date' => $data['posting_date'],
                'required_by_date' => $data['required_by_date'] ?? null,
                'total_quantity' => $totals['total_quantity'],
                'net_total' => $totals['net_total'],
                'tax_total' => $totals['tax_total'],
                'fees_total' => $totals['fees_total'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],
            ]);

            $purchaseOrder->items()->delete();
            $purchaseOrder->taxes()->delete();
            $purchaseOrder->fees()->delete();

            $this->saveItems($purchaseOrder, $data['items']);
            $this->saveTaxes($purchaseOrder, $companyId, $data['tax_template_ids'] ?? [], $totals['net_total']);
            $this->saveFees($purchaseOrder, $companyId, $data['fees_template_ids'] ?? [], $totals['net_total']);

            return $purchaseOrder->fresh()->load([
                'supplier',
                'items.item',
                'items.targetWarehouse',
                'taxes.account',
                'fees.account',
            ]);
        });
    }

    public function submit(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        if ($purchaseOrder->status !== 'draft') {
            throw new RuntimeException('Only draft purchase orders can be submitted.');
        }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $purchaseOrder->company_id,
        $purchaseOrder->posting_date,
        'create'
    );
        $purchaseOrder->update([
            'status' => 'confirmed',
        ]);

        return $purchaseOrder->fresh()->load([
            'supplier',
            'items.item',
            'items.targetWarehouse',
            'taxes.account',
            'fees.account',
        ]);
    }

public function cancel(PurchaseOrder $purchaseOrder): PurchaseOrder
{
    if ($purchaseOrder->status !== 'confirmed') {
        throw new RuntimeException(
            'Only confirmed purchase orders can be cancelled.'
        );
    }

    $purchaseOrder->update([
        'status' => 'cancelled',
    ]);

    return $purchaseOrder->fresh()->load([
        'supplier',
        'items.item',
        'items.targetWarehouse',
        'taxes.account',
        'fees.account',
    ]);
}
    public function delete(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->status !== 'draft') {
            throw new RuntimeException('Only draft purchase orders can be deleted.');
        }

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->items()->delete();
            $purchaseOrder->taxes()->delete();
            $purchaseOrder->fees()->delete();
            $purchaseOrder->delete();
        });
    }

   private function calculateTotals(int $companyId, array $data): array
{
    $totalQuantity = 0;
    $itemTotal = 0;

    foreach ($data['items'] as $row) {
        $warehouse = Warehouse::findOrFail($row['target_warehouse_id']);

        if (! $warehouse->is_group) {
            throw new RuntimeException('Target warehouse must be a main warehouse.');
        }

        $qty = (float) $row['quantity'];
        $rate = (float) $row['rate'];

        $totalQuantity += $qty;
        $itemTotal += $qty * $rate;
    }

    $discountPercentage = (float) ($data['discount_percentage'] ?? 0);

    $discountAmount = round(
        $itemTotal * ($discountPercentage / 100),
        2
    );

    $netTotal = round(
        $itemTotal - $discountAmount,
        2
    );

    $taxTotal = 0;

    foreach (($data['tax_template_ids'] ?? []) as $id) {
        $template = TaxTemplate::with('lines')
            ->where('company_id', $companyId)
            ->findOrFail($id);

        foreach ($template->lines as $line) {
            $taxTotal += $line->type === 'on_net_total'
                ? $netTotal * ((float) $line->tax_rate / 100)
                : (float) ($line->amount ?? 0);
        }
    }

    $feesTotal = 0;

    foreach (($data['fees_template_ids'] ?? []) as $id) {
        $template = FeesTemplate::where('company_id', $companyId)
            ->findOrFail($id);

        $feesTotal += $template->type === 'percentage'
            ? $netTotal * ((float) $template->fees_rate / 100)
            : (float) ($template->amount ?? 0);
    }

    $grandTotal = round(
        $netTotal + $taxTotal + $feesTotal,
        2
    );

    return [
        'total_quantity' => round($totalQuantity, 2),
        'item_total' => round($itemTotal, 2),
        'discount_amount' => $discountAmount,
        'net_total' => $netTotal,
        'tax_total' => round($taxTotal, 2),
        'fees_total' => round($feesTotal, 2),
        'grand_total' => $grandTotal,
    ];
}

    private function saveItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        foreach ($items as $row) {
            $item = Item::findOrFail($row['item_id']);

            $purchaseOrder->items()->create([
                'material_request_id' => $row['material_request_id'] ?? null,
                'item_id' => $item->id,
                'target_warehouse_id' => $row['target_warehouse_id'],
                'item_code' => $item->item_code,
                'item_name_ar' => $item->name_ar,
                'item_name_en' => $item->name_en,
                'required_by_date' => $row['required_by_date'] ?? null,
                'quantity' => $row['quantity'],
                'rate' => $row['rate'],
                'amount' => (float) $row['quantity'] * (float) $row['rate'],
            ]);
        }
    }

    private function saveTaxes(PurchaseOrder $purchaseOrder, int $companyId, array $ids, float $netTotal): void
    {
        foreach ($ids as $id) {
            $template = TaxTemplate::with('lines')
                ->where('company_id', $companyId)
                ->findOrFail($id);

            foreach ($template->lines as $line) {
                $amount = $line->type === 'on_net_total'
                    ? $netTotal * ((float) $line->tax_rate / 100)
                    : (float) ($line->amount ?? 0);

                $purchaseOrder->taxes()->create([
                    'tax_template_id' => $template->id,
                    'tax_template_line_id' => $line->id,
                    'title' => $template->title,
                    'type' => $line->type,
                    'account_id' => $line->account_id,
                    'tax_rate' => $line->tax_rate,
                    'amount' => $amount,
                ]);
            }
        }
    }

    private function saveFees(PurchaseOrder $purchaseOrder, int $companyId, array $ids, float $netTotal): void
    {
        foreach ($ids as $id) {
            $template = FeesTemplate::where('company_id', $companyId)->findOrFail($id);

            $amount = $template->type === 'percentage'
                ? $netTotal * ((float) $template->fees_rate / 100)
                : (float) ($template->amount ?? 0);

            $purchaseOrder->fees()->create([
                'fees_template_id' => $template->id,
                'title' => $template->title,
                'type' => $template->type,
                'account_id' => $template->account_id,
                'fees_rate' => $template->fees_rate,
                'amount' => $amount,
            ]);
        }
    }

    private function generateSeries(int $companyId): string
    {
        $count = PurchaseOrder::withTrashed()
            ->where('company_id', $companyId)
            ->count() + 1;

        return 'PO-' . now()->format('Y') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}