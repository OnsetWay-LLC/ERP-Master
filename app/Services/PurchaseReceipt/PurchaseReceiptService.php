<?php

namespace App\Services\PurchaseReceipt;

use App\Models\Company;
use App\Models\FeesTemplate;
use App\Models\MaterialRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceipt;
use App\Models\StockLedger;
use App\Models\TaxTemplate;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseReceiptService
{
   public function getAll()
{
    return PurchaseReceipt::with([
        'purchaseOrder',
        'supplier',
        'items.item',
        'items.acceptedWarehouse',
        'items.rejectedWarehouse',
        'taxes.account',
        'fees.account',
        'creator',
    ])
    ->where('status', '!=', 'cancelled')
    ->latest('id')
    ->paginate(request('per_page', 10));
}
    public function create(array $data): PurchaseReceipt
    {
        return DB::transaction(function () use ($data) {
            $companyId = Company::query()->value('id');
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $companyId,
        $data['posting_date'] ?? now()->toDateString(),
        'create'
    );
            $totals = $this->calculateTotals($data, $companyId);

            $receipt = PurchaseReceipt::create([
                'company_id' => $companyId,
                'purchase_order_id' => $data['purchase_order_id'],
                'supplier_id' => $data['supplier_id'],
                'receipt_number' => $this->generateReceiptNumber($companyId),
                'receipt_date' => $data['posting_date'] ?? now()->toDateString(),
                'posting_time' => $data['posting_time'] ?? now()->format('H:i:s'),

                'total_qty' => $totals['total_qty'],
                'total' => $totals['total'],
                'tax_total' => $totals['tax_total'],
                'fees_total' => $totals['fees_total'],
                'additional_discount_percentage' => $totals['discount_percentage'],
                'additional_discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],

                'status' => 'draft',
                'created_by' => auth('api')->id(),
            ]);

            foreach ($totals['items'] as $item) {
                $receipt->items()->create($item);
            }

            foreach ($totals['taxes'] as $tax) {
                $receipt->taxes()->create($tax);
            }

            foreach ($totals['fees'] as $fee) {
                $receipt->fees()->create($fee);
            }

            return $this->loadReceipt($receipt);
        });
    }

    public function update(PurchaseReceipt $receipt, array $data): PurchaseReceipt
    {
        if ($receipt->status !== 'draft') {
            throw new InvalidArgumentException('Only draft purchase receipts can be updated.');
        }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $receipt->company_id,
        $data['posting_date'] ?? $receipt->receipt_date,
        'update'
    );
        return DB::transaction(function () use ($receipt, $data) {
            $totals = $this->calculateTotals($data, $receipt->company_id);

            $receipt->update([
                'purchase_order_id' => $data['purchase_order_id'],
                'supplier_id' => $data['supplier_id'],
                'receipt_date' => $data['posting_date'] ?? $receipt->receipt_date,
                'posting_time' => $data['posting_time'] ?? $receipt->posting_time,

                'total_qty' => $totals['total_qty'],
                'total' => $totals['total'],
                'tax_total' => $totals['tax_total'],
                'fees_total' => $totals['fees_total'],
                'additional_discount_percentage' => $totals['discount_percentage'],
                'additional_discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],
            ]);

            $receipt->items()->delete();
            $receipt->taxes()->delete();
            $receipt->fees()->delete();

            foreach ($totals['items'] as $item) {
                $receipt->items()->create($item);
            }

            foreach ($totals['taxes'] as $tax) {
                $receipt->taxes()->create($tax);
            }

            foreach ($totals['fees'] as $fee) {
                $receipt->fees()->create($fee);
            }

            return $this->loadReceipt($receipt);
        });
    }

    public function submit(PurchaseReceipt $receipt): PurchaseReceipt
    {
        if ($receipt->status !== 'draft') {
            throw new InvalidArgumentException('Only draft purchase receipts can be submitted.');
        }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $receipt->company_id,
        $receipt->receipt_date,
        'create'
    );
        return DB::transaction(function () use ($receipt) {
            $receipt->load(['items', 'purchaseOrder.items']);

            foreach ($receipt->items as $item) {
                if ((float) $item->accepted_qty > 0) {
                    $stock = $this->increaseStock(
                        $receipt->company_id,
                        $item->item_id,
                        $item->accepted_warehouse_id,
                        (float) $item->accepted_qty,
                        (float) $item->rate
                    );

                    $this->createLedger(
                        $receipt->company_id,
                        $item->item_id,
                        $item->accepted_warehouse_id,
                        $receipt->id,
                        (float) $item->accepted_qty,
                        0,
                        (float) $stock->quantity,
                        (float) $item->rate,
                        (float) $item->accepted_qty * (float) $item->rate,
                        (float) $stock->stock_value
                    );
                }

                if ((float) $item->rejected_qty > 0 && $item->rejected_warehouse_id) {
                    $stock = $this->increaseStock(
                        $receipt->company_id,
                        $item->item_id,
                        $item->rejected_warehouse_id,
                        (float) $item->rejected_qty,
                        (float) $item->rate
                    );

                    $this->createLedger(
                        $receipt->company_id,
                        $item->item_id,
                        $item->rejected_warehouse_id,
                        $receipt->id,
                        (float) $item->rejected_qty,
                        0,
                        (float) $stock->quantity,
                        (float) $item->rate,
                        (float) $item->rejected_qty * (float) $item->rate,
                        (float) $stock->stock_value
                    );
                }

                $this->increaseReceivedQty($item);
            }

            $receipt->update([
                'status' => 'submitted',
            ]);

            $this->refreshPurchaseOrderStatus($receipt->purchase_order_id);

            app(\App\Services\PurchaseInvoice\PurchaseInvoiceService::class)
    ->createFromPurchaseReceipt($receipt->fresh());

            return $this->loadReceipt($receipt);
        });
    }

    public function cancel(PurchaseReceipt $receipt): PurchaseReceipt
    {
        if ($receipt->status !== 'submitted') {
            throw new InvalidArgumentException('Only submitted purchase receipts can be cancelled.');
        }
app(\App\Services\FinancialYear\FinancialYearService::class)
    ->validateTransactionDate(
        $receipt->company_id,
        $receipt->receipt_date,
        'update'
    );
        return DB::transaction(function () use ($receipt) {
            $receipt->load('items');

            foreach ($receipt->items as $item) {
                if ((float) $item->accepted_qty > 0) {
                    $stock = $this->decreaseStock(
                        $receipt->company_id,
                        $item->item_id,
                        $item->accepted_warehouse_id,
                        (float) $item->accepted_qty
                    );

                    $this->createLedger(
                        $receipt->company_id,
                        $item->item_id,
                        $item->accepted_warehouse_id,
                        $receipt->id,
                        0,
                        (float) $item->accepted_qty,
                        (float) $stock->quantity,
                        (float) $item->rate,
                        -((float) $item->accepted_qty * (float) $item->rate),
                        (float) $stock->stock_value
                    );
                }

                if ((float) $item->rejected_qty > 0 && $item->rejected_warehouse_id) {
                    $stock = $this->decreaseStock(
                        $receipt->company_id,
                        $item->item_id,
                        $item->rejected_warehouse_id,
                        (float) $item->rejected_qty
                    );

                    $this->createLedger(
                        $receipt->company_id,
                        $item->item_id,
                        $item->rejected_warehouse_id,
                        $receipt->id,
                        0,
                        (float) $item->rejected_qty,
                        (float) $stock->quantity,
                        (float) $item->rate,
                        -((float) $item->rejected_qty * (float) $item->rate),
                        (float) $stock->stock_value
                    );
                }

                $this->decreaseReceivedQty($item);
            }

            $receipt->update([
                'status' => 'cancelled',
            ]);

            $this->refreshPurchaseOrderStatusAfterCancel($receipt->purchase_order_id);

            return $this->loadReceipt($receipt);
        });
    }

    private function calculateTotals(array $data, int $companyId): array
    {
        $items = [];
        $totalQty = 0;
        $total = 0;

       foreach ($data['items'] as $row) {
    $acceptedQty = (float) $row['accepted_qty'];
    $rejectedQty = (float) ($row['rejected_qty'] ?? 0);
    $totalItemQty = $acceptedQty + $rejectedQty;

    $poItem = PurchaseOrderItem::with('item')
        ->findOrFail($row['purchase_order_item_id']);

    $rate = (float) $poItem->rate;
    $barcode = $poItem->item?->barcode;

    $amount = $totalItemQty * $rate;

    $items[] = [
        'purchase_order_item_id' => $poItem->id,
        'item_id' => $poItem->item_id,
        'barcode' => $barcode,
        'accepted_warehouse_id' => $row['accepted_warehouse_id'],
        'rejected_warehouse_id' => $row['rejected_warehouse_id'] ?? null,
        'accepted_qty' => $acceptedQty,
        'rejected_qty' => $rejectedQty,
        'total_qty' => $totalItemQty,
        'rate' => $rate,
        'amount' => $amount,
    ];

    $totalQty += $totalItemQty;
    $total += $amount;
}

        $taxes = [];
        $taxTotal = 0;

        foreach (($data['tax_template_ids'] ?? []) as $taxTemplateId) {
            $template = TaxTemplate::with('lines')
                ->where('company_id', $companyId)
                ->findOrFail($taxTemplateId);

            foreach ($template->lines as $line) {
                $amount = $line->type === 'actual'
                    ? (float) ($line->amount ?? 0)
                    : $total * ((float) $line->tax_rate / 100);

                $taxes[] = [
                    'tax_template_id' => $template->id,
                    'tax_template_line_id' => $line->id,
                    'title' => $template->title,
                    'type' => $line->type,
                    'account_id' => $line->account_id,
                    'tax_rate' => $line->tax_rate ?? 0,
                    'amount' => $amount,
                ];

                $taxTotal += $amount;
            }
        }

        $fees = [];
        $feesTotal = 0;

        foreach (($data['fees_template_ids'] ?? []) as $feesTemplateId) {
            $template = FeesTemplate::query()
                ->where('company_id', $companyId)
                ->findOrFail($feesTemplateId);

            $amount = $template->type === 'fixed_amount'
                ? (float) ($template->amount ?? 0)
                : $total * ((float) $template->fees_rate / 100);

            $fees[] = [
                'fees_template_id' => $template->id,
                'title' => $template->title,
                'type' => $template->type,
                'account_id' => $template->account_id,
                'fees_rate' => $template->fees_rate ?? 0,
                'amount' => $amount,
            ];

            $feesTotal += $amount;
        }

        $discountPercentage = (float) ($data['additional_discount_percentage'] ?? 0);

        $discountAmount = isset($data['additional_discount_amount'])
            ? (float) $data['additional_discount_amount']
            : ($total * $discountPercentage / 100);

        $grandTotal = ($total + $taxTotal + $feesTotal) - $discountAmount;

        if ($grandTotal < 0) {
            throw new InvalidArgumentException('Grand total cannot be negative.');
        }

        return [
            'items' => $items,
            'taxes' => $taxes,
            'fees' => $fees,
            'total_qty' => $totalQty,
            'total' => $total,
            'tax_total' => $taxTotal,
            'fees_total' => $feesTotal,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
        ];
    }

    private function increaseReceivedQty($receiptItem): void
    {
        $receivedQty = (float) $receiptItem->total_qty;

        $poItem = PurchaseOrderItem::query()
            ->lockForUpdate()
            ->findOrFail($receiptItem->purchase_order_item_id);

        $poItem->increment('received_qty', $receivedQty);

        if ($poItem->material_request_item_id) {
            $mrItem = MaterialRequestItem::query()
                ->lockForUpdate()
                ->findOrFail($poItem->material_request_item_id);

            $mrItem->increment('received_qty', $receivedQty);
            $this->refreshMaterialRequestItemStatus($mrItem->fresh());
        }
    }

    private function decreaseReceivedQty($receiptItem): void
    {
        $receivedQty = (float) $receiptItem->total_qty;

        $poItem = PurchaseOrderItem::query()
            ->lockForUpdate()
            ->findOrFail($receiptItem->purchase_order_item_id);

        $poItem->decrement('received_qty', $receivedQty);

        if ($poItem->material_request_item_id) {
            $mrItem = MaterialRequestItem::query()
                ->lockForUpdate()
                ->findOrFail($poItem->material_request_item_id);

            $mrItem->decrement('received_qty', $receivedQty);
            $this->refreshMaterialRequestItemStatus($mrItem->fresh());
        }
    }

    private function refreshMaterialRequestItemStatus(MaterialRequestItem $mrItem): void
    {
        if ((float) $mrItem->received_qty <= 0) {
            if ((float) $mrItem->ordered_qty > 0) {
                $mrItem->update(['status' => 'ordered']);
            } else {
                $mrItem->update(['status' => 'pending']);
            }
            return;
        }

        if ((float) $mrItem->received_qty < (float) $mrItem->required_qty) {
            $mrItem->update(['status' => 'partially_received']);
            return;
        }

        $mrItem->update(['status' => 'received']);
    }

    private function refreshPurchaseOrderStatus(int $purchaseOrderId): void
    {
        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($purchaseOrderId);

        $allReceived = $purchaseOrder->items->every(function ($item) {
            return (float) $item->received_qty >= (float) $item->quantity;
        });

        if ($allReceived) {
            $purchaseOrder->update([
                'status' => 'completed',
            ]);
        }
    }

    private function refreshPurchaseOrderStatusAfterCancel(int $purchaseOrderId): void
    {
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);

        if ($purchaseOrder->status === 'completed') {
            $purchaseOrder->update([
                'status' => 'confirmed',
            ]);
        }
    }

    private function increaseStock(
        int $companyId,
        int $itemId,
        int $warehouseId,
        float $quantity,
        float $rate
    ): WarehouseStock {
        $stock = WarehouseStock::firstOrCreate(
            [
                'company_id' => $companyId,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'average_rate' => 0,
                'stock_value' => 0,
            ]
        );

        $oldQty = (float) $stock->quantity;
        $oldValue = (float) $stock->stock_value;

        $incomingValue = $quantity * $rate;

        $newQty = $oldQty + $quantity;
        $newValue = $oldValue + $incomingValue;
        $newAverageRate = $newQty > 0 ? $newValue / $newQty : 0;

        $stock->update([
            'quantity' => $newQty,
            'average_rate' => $newAverageRate,
            'stock_value' => $newValue,
        ]);

        return $stock->fresh();
    }

    private function decreaseStock(
        int $companyId,
        int $itemId,
        int $warehouseId,
        float $quantity
    ): WarehouseStock {
        $stock = WarehouseStock::query()
            ->where('company_id', $companyId)
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if (! $stock || (float) $stock->quantity < $quantity) {
            throw new InvalidArgumentException('Insufficient stock quantity to cancel purchase receipt.');
        }

        $averageRate = (float) $stock->average_rate;
        $outgoingValue = $quantity * $averageRate;

        $newQty = (float) $stock->quantity - $quantity;
        $newValue = (float) $stock->stock_value - $outgoingValue;

        $stock->update([
            'quantity' => $newQty,
            'stock_value' => max($newValue, 0),
            'average_rate' => $newQty > 0 ? $averageRate : 0,
        ]);

        return $stock->fresh();
    }

    private function createLedger(
        int $companyId,
        int $itemId,
        int $warehouseId,
        int $receiptId,
        float $quantityIn,
        float $quantityOut,
        float $balanceQty,
        float $basicRate,
        float $stockValue,
        float $balanceValue
    ): void {
        StockLedger::create([
            'company_id' => $companyId,
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'entry_date' => now()->toDateString(),
            'reference_type' => 'purchase_receipt',
            'reference_id' => $receiptId,
            'quantity_in' => $quantityIn,
            'quantity_out' => $quantityOut,
            'balance_qty' => $balanceQty,
            'basic_rate' => $basicRate,
            'stock_value' => $stockValue,
            'balance_value' => $balanceValue,
        ]);
    }

    private function generateReceiptNumber(int $companyId): string
    {
        $lastId = PurchaseReceipt::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'PR-' . now()->format('Y') . '-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }

    private function loadReceipt(PurchaseReceipt $receipt): PurchaseReceipt
    {
        return $receipt->fresh()->load([
            'purchaseOrder',
            'supplier',
            'items.item',
            'items.acceptedWarehouse',
            'items.rejectedWarehouse',
            'taxes.account',
            'fees.account',
            'creator',
        ]);
    }
}