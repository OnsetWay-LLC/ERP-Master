<?php

namespace App\Services\SalesOrder;

use App\Models\Company;
use App\Models\FeesTemplate;
use App\Models\Item;
use App\Models\PickList;
use App\Models\SalesOrder;
use App\Models\TaxTemplate;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesOrderService
{
    public function getAll()
    {
        $companyId = Company::query()->firstOrFail()->id;

        return SalesOrder::query()
            ->with([
                'customer',
                'items.item',
                'items.warehouse',
                'taxes',
                'fees',
                'pickList',
            ])
            ->where('company_id', $companyId)
            ->latest()
            ->get();
    }

    public function show(SalesOrder $salesOrder): SalesOrder
    {
        return $salesOrder->load([
            'customer',
            'items.item',
            'items.warehouse',
            'taxes.account',
            'fees.account',
            'creator',
            'pickList.items.warehouse',
        ]);
    }

    public function create(array $data): SalesOrder
    {
        return DB::transaction(function () use ($data) {
            $company = Company::query()->firstOrFail();

            $totals = $this->calculateTotals($company->id, $data);

            $salesOrder = SalesOrder::query()->create([
                'company_id' => $company->id,
                'customer_id' => $data['customer_id'],
                'order_number' => $this->generateOrderNumber($company->id),
                'order_date' => $data['order_date'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'net_total' => $totals['net_total'],
                'tax_total' => $totals['tax_total'],
                'fees_total' => $totals['fees_total'],
                'discount_percentage' => $totals['discount_percentage'],
                'discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],
                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ]);

            $this->saveItems($salesOrder, $company->id, $data['items']);
            $this->saveTaxes($salesOrder, $company->id, $data['tax_template_ids'] ?? [], $totals['net_total']);
            $this->saveFees($salesOrder, $company->id, $data['fees_template_ids'] ?? [], $totals['net_total']);

            return $salesOrder->fresh()->load([
                'customer',
                'items.item',
                'items.warehouse',
                'taxes',
                'fees',
                'pickList',
            ]);
        });
    }

    public function update(SalesOrder $salesOrder, array $data): SalesOrder
    {
        if ($salesOrder->status !== 'draft') {
            throw new RuntimeException('Only draft sales orders can be updated.');
        }

        return DB::transaction(function () use ($salesOrder, $data) {
            $companyId = $salesOrder->company_id;

            $totals = $this->calculateTotals($companyId, $data);

            $salesOrder->update([
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'net_total' => $totals['net_total'],
                'tax_total' => $totals['tax_total'],
                'fees_total' => $totals['fees_total'],
                'discount_percentage' => $totals['discount_percentage'],
                'discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],
            ]);

            $salesOrder->items()->delete();
            $salesOrder->taxes()->delete();
            $salesOrder->fees()->delete();

            $this->saveItems($salesOrder, $companyId, $data['items']);
            $this->saveTaxes($salesOrder, $companyId, $data['tax_template_ids'] ?? [], $totals['net_total']);
            $this->saveFees($salesOrder, $companyId, $data['fees_template_ids'] ?? [], $totals['net_total']);

            return $salesOrder->fresh()->load([
                'customer',
                'items.item',
                'items.warehouse',
                'taxes',
                'fees',
                'pickList',
            ]);
        });
    }

    public function submit(SalesOrder $salesOrder): SalesOrder
    {
        if ($salesOrder->status !== 'draft') {
            throw new RuntimeException('Only draft sales orders can be submitted.');
        }

        return DB::transaction(function () use ($salesOrder) {
            $salesOrder->load(['items', 'customer']);

            foreach ($salesOrder->items as $item) {
                $stock = $this->getStockForUpdate(
                    $salesOrder->company_id,
                    $item->item_id,
                    $item->warehouse_id
                );

                $available = $stock->quantity - $stock->reserved_quantity;

                if ($available < $item->quantity) {
                    throw new RuntimeException(
                        'Requested quantity is greater than available stock for item: ' . $item->item_name_en
                    );
                }

                $stock->increment('reserved_quantity', $item->quantity);
            }

            $salesOrder->update([
                'status' => 'delivery_and_to_bill',
            ]);

            $this->createPickListAutomatically($salesOrder);

            return $salesOrder->fresh()->load([
                'customer',
                'items.item',
                'items.warehouse',
                'taxes',
                'fees',
                'creator',
                'pickList.items.warehouse',
            ]);
        });
    }

    public function cancel(SalesOrder $salesOrder): SalesOrder
    {
        if (in_array($salesOrder->status, ['to_bill', 'completed'])) {
            throw new RuntimeException('This sales order cannot be cancelled.');
        }

        return DB::transaction(function () use ($salesOrder) {
            if ($salesOrder->status === 'delivery_and_to_bill') {
                $salesOrder->load('items');

                foreach ($salesOrder->items as $item) {
                    $stock = $this->getStockForUpdate(
                        $salesOrder->company_id,
                        $item->item_id,
                        $item->warehouse_id
                    );

                    if ($stock->reserved_quantity < $item->quantity) {
                        throw new RuntimeException(
                            'Reserved quantity is not enough for item: ' . $item->item_name_en
                        );
                    }

                    $stock->decrement('reserved_quantity', $item->quantity);
                }

                PickList::query()
                    ->where('sales_order_id', $salesOrder->id)
                    ->where('status', 'open')
                    ->update(['status' => 'cancelled']);
            }

            $salesOrder->update([
                'status' => 'cancelled',
            ]);

            return $salesOrder->fresh()->load([
                'customer',
                'items.item',
                'items.warehouse',
                'taxes',
                'fees',
                'pickList',
            ]);
        });
    }

    public function delete(SalesOrder $salesOrder): void
    {
        if ($salesOrder->status !== 'draft') {
            throw new RuntimeException('Only draft sales orders can be deleted.');
        }

        DB::transaction(function () use ($salesOrder) {
            $salesOrder->items()->delete();
            $salesOrder->taxes()->delete();
            $salesOrder->fees()->delete();
            $salesOrder->delete();
        });
    }

    private function createPickListAutomatically(SalesOrder $salesOrder): void
    {
        $salesOrder->loadMissing('items');

        $exists = PickList::query()
            ->where('sales_order_id', $salesOrder->id)
            ->exists();

        if ($exists) {
            return;
        }

        $pickList = PickList::query()->create([
            'company_id' => $salesOrder->company_id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'pick_list_number' => $this->generatePickListNumber($salesOrder->company_id),
            'posting_date' => now()->toDateString(),
            'status' => 'open',
            'created_by' => auth('api')->id(),
        ]);

        foreach ($salesOrder->items as $item) {
            $pickList->items()->create([
                'sales_order_item_id' => $item->id,
                'item_id' => $item->item_id,
                'warehouse_id' => $item->warehouse_id,
                'item_code' => $item->item_code,
                'item_name_ar' => $item->item_name_ar,
                'item_name_en' => $item->item_name_en,
                'required_quantity' => $item->quantity,
                'picked_quantity' => $item->quantity,
            ]);
        }
    }

    private function saveItems(SalesOrder $salesOrder, int $companyId, array $items): void
    {
        foreach ($items as $row) {
            $item = Item::query()->findOrFail($row['item_id']);

            $stock = WarehouseStock::query()
                ->where('company_id', $companyId)
                ->where('item_id', $row['item_id'])
                ->where('warehouse_id', $row['warehouse_id'])
                ->first();

            if (!$stock) {
                throw new RuntimeException('Item does not have stock in selected warehouse.');
            }

            $available = $stock->quantity - $stock->reserved_quantity;

            if ($available <= 0) {
                throw new RuntimeException('This item is out of stock.');
            }

            if ($row['quantity'] > $available) {
                throw new RuntimeException('Requested quantity is greater than available stock.');
            }

            $salesOrder->items()->create([
                'item_id' => $row['item_id'],
                'warehouse_id' => $row['warehouse_id'],
                'item_code' => $item->item_code ?? null,
                'item_name_ar' => $item->name_ar ?? null,
                'item_name_en' => $item->name_en ?? null,
                'available_stock' => $available,
                'quantity' => $row['quantity'],
                'rate' => $row['rate'],
                'amount' => $row['quantity'] * $row['rate'],
            ]);
        }
    }

    private function saveTaxes(SalesOrder $salesOrder, int $companyId, array $taxTemplateIds, float $netTotal): void
    {
        if (empty($taxTemplateIds)) {
            return;
        }

        $templates = TaxTemplate::query()
            ->with('lines')
            ->where('company_id', $companyId)
            ->whereIn('id', $taxTemplateIds)
            ->get();

        foreach ($templates as $template) {
            foreach ($template->lines as $line) {
                $amount = $line->type === 'on_net_total'
                    ? $netTotal * (($line->tax_rate ?? 0) / 100)
                    : ($line->amount ?? 0);

                $salesOrder->taxes()->create([
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

    private function saveFees(SalesOrder $salesOrder, int $companyId, array $feesTemplateIds, float $netTotal): void
    {
        if (empty($feesTemplateIds)) {
            return;
        }

        $templates = FeesTemplate::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $feesTemplateIds)
            ->where('is_active', true)
            ->get();

        foreach ($templates as $template) {
            $amount = $template->type === 'percentage'
                ? $netTotal * (($template->fees_rate ?? 0) / 100)
                : ($template->amount ?? 0);

            $salesOrder->fees()->create([
                'fees_template_id' => $template->id,
                'title' => $template->title,
                'type' => $template->type,
                'account_id' => $template->account_id,
                'fees_rate' => $template->fees_rate,
                'amount' => $amount,
            ]);
        }
    }

    private function calculateTotals(int $companyId, array $data): array
    {
        $netTotal = 0;

        foreach ($data['items'] as $item) {
            $netTotal += $item['quantity'] * $item['rate'];
        }

        $taxTotal = $this->calculateTaxTotal(
            $companyId,
            $data['tax_template_ids'] ?? [],
            $netTotal
        );

        $feesTotal = $this->calculateFeesTotal(
            $companyId,
            $data['fees_template_ids'] ?? [],
            $netTotal
        );

        $discountPercentage = $data['discount_percentage'] ?? 0;
        $discountAmount = $netTotal * ($discountPercentage / 100);

        $grandTotal = ($netTotal + $taxTotal + $feesTotal) - $discountAmount;

        return [
            'net_total' => $netTotal,
            'tax_total' => $taxTotal,
            'fees_total' => $feesTotal,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
        ];
    }

    private function calculateTaxTotal(int $companyId, array $taxTemplateIds, float $netTotal): float
    {
        if (empty($taxTemplateIds)) {
            return 0;
        }

        $total = 0;

        $templates = TaxTemplate::query()
            ->with('lines')
            ->where('company_id', $companyId)
            ->whereIn('id', $taxTemplateIds)
            ->get();

        foreach ($templates as $template) {
            foreach ($template->lines as $line) {
                $total += $line->type === 'on_net_total'
                    ? $netTotal * (($line->tax_rate ?? 0) / 100)
                    : ($line->amount ?? 0);
            }
        }

        return $total;
    }

    private function calculateFeesTotal(int $companyId, array $feesTemplateIds, float $netTotal): float
    {
        if (empty($feesTemplateIds)) {
            return 0;
        }

        $total = 0;

        $templates = FeesTemplate::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $feesTemplateIds)
            ->where('is_active', true)
            ->get();

        foreach ($templates as $template) {
            $total += $template->type === 'percentage'
                ? $netTotal * (($template->fees_rate ?? 0) / 100)
                : ($template->amount ?? 0);
        }

        return $total;
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

    private function generateOrderNumber(int $companyId): string
    {
        $count = SalesOrder::query()
            ->where('company_id', $companyId)
            ->withTrashed()
            ->count() + 1;

        return 'SO-' . now()->format('Y') . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    private function generatePickListNumber(int $companyId): string
    {
        $count = PickList::query()
            ->where('company_id', $companyId)
            ->count() + 1;

        return 'PL-' . now()->format('Y') . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}