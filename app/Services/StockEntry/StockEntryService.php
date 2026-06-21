<?php

namespace App\Services\StockEntry;

use App\Models\CompanyAccountSetting;
use App\Models\Item;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\StockEntry;
use App\Models\StockEntryItem;
use App\Models\StockLedger;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\GeneralLedger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockEntryService
{
    public function getAll(int $companyId, array $filters = []): Collection
    {
        return StockEntry::query()
            ->where('company_id', $companyId)
            ->with([
                'items.item',
                'items.sourceWarehouse',
                'items.targetWarehouse',
            ])
            ->when($filters['entry_type'] ?? null, fn ($q, $v) => $q->where('entry_type', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->latest('id')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): StockEntry
    {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $entry = StockEntry::create([
                'company_id' => $companyId,
                'series' => $this->generateSeries($companyId),
                'entry_type' => $data['entry_type'],
                'posting_date' => $data['posting_date'] ?? now()->toDateString(),
                'posting_time' => $data['posting_time'] ?? now()->format('H:i:s'),
                'total_incoming_value' => 0,
                'total_outgoing_value' => 0,
                'value_difference' => 0,
                'status' => 'draft',
                'created_by' => $createdBy,
            ]);

            foreach ($data['items'] as $row) {
                StockEntryItem::create([
                    'stock_entry_id' => $entry->id,
                    'item_id' => $row['item_id'],
                    'barcode' => $row['barcode'] ?? null,
                    'source_warehouse_id' => $row['source_warehouse_id'] ?? null,
                    'target_warehouse_id' => $row['target_warehouse_id'] ?? null,
                    'quantity' => $row['quantity'],
                    'basic_rate' => 0,
                    'incoming_value' => 0,
                    'outgoing_value' => 0,
                    'value_difference' => 0,
                ]);
            }

            return $entry->fresh([
                'items.item',
                'items.sourceWarehouse',
                'items.targetWarehouse',
            ]);
        });
    }

    public function show(int $companyId, int $id): StockEntry
    {
        return StockEntry::query()
            ->where('company_id', $companyId)
            ->with([
                'items.item',
                'items.sourceWarehouse',
                'items.targetWarehouse',
            ])
            ->findOrFail($id);
    }

    public function updateDraft(int $companyId, int $id, array $data): StockEntry
    {
        return DB::transaction(function () use ($companyId, $id, $data) {
            $entry = StockEntry::query()
                ->where('company_id', $companyId)
                ->with('items')
                ->findOrFail($id);

            if ($entry->status !== 'draft') {
                throw new InvalidArgumentException('Only draft stock entries can be updated.');
            }

            $entry->update([
                'entry_type' => $data['entry_type'],
                'posting_date' => $data['posting_date'] ?? $entry->posting_date,
                'posting_time' => $data['posting_time'] ?? $entry->posting_time,
            ]);

            $entry->items()->delete();

            foreach ($data['items'] as $row) {
                StockEntryItem::create([
                    'stock_entry_id' => $entry->id,
                    'item_id' => $row['item_id'],
                    'barcode' => $row['barcode'] ?? null,
                    'source_warehouse_id' => $row['source_warehouse_id'] ?? null,
                    'target_warehouse_id' => $row['target_warehouse_id'] ?? null,
                    'quantity' => $row['quantity'],
                    'basic_rate' => 0,
                    'incoming_value' => 0,
                    'outgoing_value' => 0,
                    'value_difference' => 0,
                ]);
            }

            return $entry->fresh([
                'items.item',
                'items.sourceWarehouse',
                'items.targetWarehouse',
            ]);
        });
    }

    public function submit(int $companyId, int $id): StockEntry
    {
        return DB::transaction(function () use ($companyId, $id) {
            $entry = StockEntry::query()
                ->where('company_id', $companyId)
                ->with('items')
                ->findOrFail($id);

            if ($entry->status !== 'draft') {
                throw new InvalidArgumentException('Only draft stock entries can be submitted.');
            }

            $totalIncoming = 0;
            $totalOutgoing = 0;

            foreach ($entry->items as $itemRow) {
                $row = [
                    'item_id' => $itemRow->item_id,
                    'barcode' => $itemRow->barcode,
                    'source_warehouse_id' => $itemRow->source_warehouse_id,
                    'target_warehouse_id' => $itemRow->target_warehouse_id,
                    'quantity' => $itemRow->quantity,
                ];

                $itemRow->delete();

                $result = $this->processItemRow(
                    companyId: $companyId,
                    stockEntry: $entry,
                    entryType: $entry->entry_type,
                    row: $row
                );

                $totalIncoming += $result['incoming_value'];
                $totalOutgoing += $result['outgoing_value'];
            }

            $entry->update([
                'total_incoming_value' => $totalIncoming,
                'total_outgoing_value' => $totalOutgoing,
                'value_difference' => $totalIncoming - $totalOutgoing,
                'status' => 'submitted',
            ]);

           $entry = $entry->fresh([
    'items.item',
    'items.sourceWarehouse',
    'items.targetWarehouse',
]);

if ($entry->entry_type === 'material_receipt') {
    $this->createMaterialReceiptJournalEntry($companyId, $entry);
}

if ($entry->entry_type === 'material_issue') {
    $this->createMaterialIssueJournalEntry($companyId, $entry);
}

            return $entry->fresh([
                'items.item',
                'items.sourceWarehouse',
                'items.targetWarehouse',
            ]);
        });
    }

    public function cancel(int $companyId, int $id): StockEntry
{
    return DB::transaction(function () use ($companyId, $id) {
        $entry = StockEntry::query()
            ->where('company_id', $companyId)
            ->with('items')
            ->findOrFail($id);

        if ($entry->status !== 'submitted') {
            throw new InvalidArgumentException('Only submitted stock entries can be cancelled.');
        }

        foreach ($entry->items as $item) {
            if ($entry->entry_type === 'material_receipt') {
                $this->decreaseStock(
                    $companyId,
                    $item->item_id,
                    $item->target_warehouse_id,
                    (float) $item->quantity
                );

                $this->createReverseLedger($companyId, $entry, $item, 'receipt_cancel');
            }

            if ($entry->entry_type === 'material_issue') {
                $this->increaseStock(
                    $companyId,
                    $item->item_id,
                    $item->source_warehouse_id,
                    (float) $item->quantity,
                    (float) $item->basic_rate
                );

                $this->createReverseLedger($companyId, $entry, $item, 'issue_cancel');
            }

            if ($entry->entry_type === 'material_transfer') {
                $this->decreaseStock(
                    $companyId,
                    $item->item_id,
                    $item->target_warehouse_id,
                    (float) $item->quantity
                );

                $this->increaseStock(
                    $companyId,
                    $item->item_id,
                    $item->source_warehouse_id,
                    (float) $item->quantity,
                    (float) $item->basic_rate
                );

                $this->createReverseLedger($companyId, $entry, $item, 'transfer_cancel');
            }
        }

        if ($entry->entry_type === 'material_receipt') {
            $this->createMaterialReceiptReverseJournalEntry($companyId, $entry);
        }

        if ($entry->entry_type === 'material_issue') {
            $this->createMaterialIssueReverseJournalEntry($companyId, $entry);
        }

        $entry->update([
            'status' => 'cancelled',
        ]);

        return $entry->fresh([
            'items.item',
            'items.sourceWarehouse',
            'items.targetWarehouse',
        ]);
    });
}
   

   private function processItemRow(
    int $companyId,
    StockEntry $stockEntry,
    string $entryType,
    array $row
): array {
    $itemId = (int) $row['item_id'];
    $quantity = (float) $row['quantity'];

    $item = Item::query()
        ->where('id', $itemId)
        ->where('company_id', $companyId)
        ->whereNull('deleted_at')
        ->first();

    if (! $item) {
        throw new InvalidArgumentException('Selected item is invalid or deleted.');
    }

    $rate = $this->getBasicRate($companyId, $itemId, $row['source_warehouse_id'] ?? null);

    return match ($entryType) {
        'material_receipt' => $this->processReceipt($companyId, $stockEntry, $row, $quantity, $rate),
        'material_issue' => $this->processIssue($companyId, $stockEntry, $row, $quantity, $rate),
        'material_transfer' => $this->processTransfer($companyId, $stockEntry, $row, $quantity, $rate),
        default => throw new InvalidArgumentException('Invalid stock entry type.'),
    };
}
private function processReceipt(
    int $companyId,
    StockEntry $entry,
    array $row,
    float $quantity,
    float $rate
): array {
    if (empty($row['target_warehouse_id'])) {
        throw new InvalidArgumentException('Target warehouse is required for material receipt.');
    }

    $this->ensureParentWarehouse(
        $companyId,
        (int) $row['target_warehouse_id'],
        'Material receipt target warehouse must be a parent warehouse.'
    );

    $incoming = $quantity * $rate;

    StockEntryItem::create([
        'stock_entry_id' => $entry->id,
        'item_id' => $row['item_id'],
        'barcode' => $row['barcode'] ?? null,
        'source_warehouse_id' => null,
        'target_warehouse_id' => $row['target_warehouse_id'],
        'quantity' => $quantity,
        'basic_rate' => $rate,
        'incoming_value' => $incoming,
        'outgoing_value' => 0,
        'value_difference' => $incoming,
    ]);

    $stock = $this->increaseStock(
        $companyId,
        (int) $row['item_id'],
        (int) $row['target_warehouse_id'],
        $quantity,
        $rate
    );

    $this->createLedger(
        companyId: $companyId,
        itemId: (int) $row['item_id'],
        warehouseId: (int) $row['target_warehouse_id'],
        entryDate: $entry->posting_date->format('Y-m-d'),
        referenceId: $entry->id,
        quantityIn: $quantity,
        quantityOut: 0,
        balanceQty: (float) $stock->quantity,
        basicRate: $rate,
        stockValue: $incoming,
        balanceValue: (float) $stock->stock_value
    );

    return [
        'incoming_value' => $incoming,
        'outgoing_value' => 0,
    ];
}
    private function processIssue(
        int $companyId,
        StockEntry $entry,
        array $row,
        float $quantity,
        float $rate
    ): array {
        if (empty($row['source_warehouse_id'])) {
            throw new InvalidArgumentException('Source warehouse is required for material issue.');
        }

        $this->ensureParentWarehouse(
            $companyId,
            (int) $row['source_warehouse_id'],
            'Material issue source warehouse must be a parent warehouse.'
        );

        $outgoing = $quantity * $rate;

        $stock = $this->decreaseStock(
            $companyId,
            (int) $row['item_id'],
            (int) $row['source_warehouse_id'],
            $quantity
        );

        StockEntryItem::create([
            'stock_entry_id' => $entry->id,
            'item_id' => $row['item_id'],
            'barcode' => $row['barcode'] ?? null,
            'source_warehouse_id' => $row['source_warehouse_id'],
            'target_warehouse_id' => null,
            'quantity' => $quantity,
            'basic_rate' => $rate,
            'incoming_value' => 0,
            'outgoing_value' => $outgoing,
            'value_difference' => -$outgoing,
        ]);

        $this->createLedger(
            companyId: $companyId,
            itemId: (int) $row['item_id'],
            warehouseId: (int) $row['source_warehouse_id'],
            entryDate: $entry->posting_date->format('Y-m-d'),
            referenceId: $entry->id,
            quantityIn: 0,
            quantityOut: $quantity,
            balanceQty: (float) $stock->quantity,
            basicRate: $rate,
            stockValue: -$outgoing,
            balanceValue: (float) $stock->stock_value
        );

        return [
            'incoming_value' => 0,
            'outgoing_value' => $outgoing,
        ];
    }

    private function processTransfer(
        int $companyId,
        StockEntry $entry,
        array $row,
        float $quantity,
        float $rate
    ): array {
        if (empty($row['source_warehouse_id']) || empty($row['target_warehouse_id'])) {
            throw new InvalidArgumentException('Source and target warehouses are required for material transfer.');
        }

        if ((int) $row['source_warehouse_id'] === (int) $row['target_warehouse_id']) {
            throw new InvalidArgumentException('Source and target warehouses cannot be the same.');
        }

        $sourceWarehouse = $this->getWarehouseOrFail(
            $companyId,
            (int) $row['source_warehouse_id'],
            'Invalid source warehouse.'
        );

        $targetWarehouse = $this->getWarehouseOrFail(
            $companyId,
            (int) $row['target_warehouse_id'],
            'Invalid target warehouse.'
        );

        $sourceIsChild = ! (bool) $sourceWarehouse->is_group;
        $targetIsChild = ! (bool) $targetWarehouse->is_group;

        if ($sourceIsChild && $targetIsChild) {
            throw new InvalidArgumentException('Transfer from child warehouse to child warehouse is not allowed.');
        }

        $value = $quantity * $rate;

        $sourceStock = $this->decreaseStock(
            $companyId,
            (int) $row['item_id'],
            (int) $row['source_warehouse_id'],
            $quantity
        );

        $targetStock = $this->increaseStock(
            $companyId,
            (int) $row['item_id'],
            (int) $row['target_warehouse_id'],
            $quantity,
            $rate
        );

        StockEntryItem::create([
            'stock_entry_id' => $entry->id,
            'item_id' => $row['item_id'],
            'barcode' => $row['barcode'] ?? null,
            'source_warehouse_id' => $row['source_warehouse_id'],
            'target_warehouse_id' => $row['target_warehouse_id'],
            'quantity' => $quantity,
            'basic_rate' => $rate,
            'incoming_value' => $value,
            'outgoing_value' => $value,
            'value_difference' => 0,
        ]);

        $this->createLedger(
            companyId: $companyId,
            itemId: (int) $row['item_id'],
            warehouseId: (int) $row['source_warehouse_id'],
            entryDate: $entry->posting_date->format('Y-m-d'),
            referenceId: $entry->id,
            quantityIn: 0,
            quantityOut: $quantity,
            balanceQty: (float) $sourceStock->quantity,
            basicRate: $rate,
            stockValue: -$value,
            balanceValue: (float) $sourceStock->stock_value
        );

        $this->createLedger(
            companyId: $companyId,
            itemId: (int) $row['item_id'],
            warehouseId: (int) $row['target_warehouse_id'],
            entryDate: $entry->posting_date->format('Y-m-d'),
            referenceId: $entry->id,
            quantityIn: $quantity,
            quantityOut: 0,
            balanceQty: (float) $targetStock->quantity,
            basicRate: $rate,
            stockValue: $value,
            balanceValue: (float) $targetStock->stock_value
        );

        return [
            'incoming_value' => $value,
            'outgoing_value' => $value,
        ];
    }

    private function createMaterialReceiptJournalEntry(int $companyId, StockEntry $entry): void
{
    $amount = (float) $entry->total_incoming_value;

    if ($amount <= 0) {
        return;
    }

    $settings = CompanyAccountSetting::query()
        ->where('company_id', $companyId)
        ->first();

    if (
        ! $settings ||
        ! $settings->default_inventory_account_id ||
        ! $settings->inventory_adjustment_account_id
    ) {
        throw new InvalidArgumentException(
            'Default inventory account and inventory adjustment account must be configured.'
        );
    }

    $journalEntry = JournalEntry::create([
        'company_id' => $companyId,
        'entry_number' => $this->generateJournalEntryNumber($companyId),
        'entry_date' => $entry->posting_date,
        'total_debit' => $amount,
        'total_credit' => $amount,
        'posted_at' => now(),
        'description' => 'Automatic journal entry for Material Receipt: ' . $entry->series,
        'status' => 'posted',
        'created_by' => $entry->created_by,
    ]);

   $debitLine = JournalEntryLine::create([
    'company_id' => $companyId,
    'journal_entry_id' => $journalEntry->id,
    'account_id' => $settings->default_inventory_account_id,
    'party_type' => null,
    'party_id' => null,
    'debit' => $amount,
    'credit' => 0,
    'note' => 'Dr Inventory Account - Material Receipt',
]);

$this->createGeneralLedgerEntry($journalEntry, $debitLine);

$creditLine = JournalEntryLine::create([
    'company_id' => $companyId,
    'journal_entry_id' => $journalEntry->id,
    'account_id' => $settings->inventory_adjustment_account_id,
    'party_type' => null,
    'party_id' => null,
    'debit' => 0,
    'credit' => $amount,
    'note' => 'Cr Inventory Adjustment Account - Material Receipt',
]);

$this->createGeneralLedgerEntry($journalEntry, $creditLine);
}

private function createMaterialIssueJournalEntry(int $companyId, StockEntry $entry): void
{
    $amount = (float) $entry->total_outgoing_value;

    if ($amount <= 0) {
        return;
    }

    $settings = CompanyAccountSetting::query()
        ->where('company_id', $companyId)
        ->first();

    if (
        ! $settings ||
        ! $settings->default_inventory_account_id ||
        ! $settings->inventory_adjustment_account_id
    ) {
        throw new InvalidArgumentException(
            'Default inventory account and inventory adjustment account must be configured.'
        );
    }

    $journalEntry = JournalEntry::create([
        'company_id' => $companyId,
        'entry_number' => $this->generateJournalEntryNumber($companyId),
        'entry_date' => $entry->posting_date,
        'total_debit' => $amount,
        'total_credit' => $amount,
        'posted_at' => now(),
        'description' => 'Automatic journal entry for Material Issue: ' . $entry->series,
        'status' => 'posted',
        'created_by' => $entry->created_by,
    ]);

   $debitLine = JournalEntryLine::create([
    'company_id' => $companyId,
    'journal_entry_id' => $journalEntry->id,
    'account_id' => $settings->inventory_adjustment_account_id,
    'party_type' => null,
    'party_id' => null,
    'debit' => $amount,
    'credit' => 0,
    'note' => 'Dr Inventory Adjustment Account - Material Issue',
]);

$this->createGeneralLedgerEntry($journalEntry, $debitLine);

$creditLine = JournalEntryLine::create([
    'company_id' => $companyId,
    'journal_entry_id' => $journalEntry->id,
    'account_id' => $settings->default_inventory_account_id,
    'party_type' => null,
    'party_id' => null,
    'debit' => 0,
    'credit' => $amount,
    'note' => 'Cr Inventory Account - Material Issue',
]);

$this->createGeneralLedgerEntry($journalEntry, $creditLine);
}
private function createGeneralLedgerEntry(
    JournalEntry $journalEntry,
    JournalEntryLine $line
): void {
    GeneralLedger::create([
        'company_id' => $line->company_id,
        'journal_entry_id' => $journalEntry->id,
        'journal_entry_line_id' => $line->id,
        'account_id' => $line->account_id,
        'entry_date' => $journalEntry->entry_date,
        'debit' => $line->debit,
        'credit' => $line->credit,
        'balance' => (float) $line->debit - (float) $line->credit,
        'description' => $line->note,
        'created_by' => $journalEntry->created_by,
    ]);
}
private function generateJournalEntryNumber(int $companyId): string
{
    $lastId = JournalEntry::query()
        ->where('company_id', $companyId)
        ->max('id') ?? 0;

    return 'JV-' . now()->format('Y') . '-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
}
    private function ensureParentWarehouse(
        int $companyId,
        int $warehouseId,
        string $message
    ): Warehouse {
        $warehouse = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('id', $warehouseId)
            ->where('is_group', true)
            ->whereNull('deleted_at')
            ->first();

        if (! $warehouse) {
            throw new InvalidArgumentException($message);
        }

        return $warehouse;
    }

    private function getWarehouseOrFail(
        int $companyId,
        int $warehouseId,
        string $message
    ): Warehouse {
        $warehouse = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('id', $warehouseId)
            ->whereNull('deleted_at')
            ->first();

        if (! $warehouse) {
            throw new InvalidArgumentException($message);
        }

        return $warehouse;
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
            throw new InvalidArgumentException('Insufficient stock quantity in selected warehouse.');
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

   private function getBasicRate(int $companyId, int $itemId, ?int $warehouseId = null): float
{
    if ($warehouseId) {
        $stock = WarehouseStock::query()
            ->where('company_id', $companyId)
            ->where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($stock && (float) $stock->average_rate > 0) {
            return (float) $stock->average_rate;
        }
    }

    $item = Item::query()
        ->where('company_id', $companyId)
        ->where('id', $itemId)
        ->whereNull('deleted_at')
        ->first();

    if (! $item) {
        throw new InvalidArgumentException('Selected item is invalid or deleted.');
    }

    return (float) ($item->purchase_price ?? $item->selling_price ?? 0);
}

    private function createLedger(
        int $companyId,
        int $itemId,
        int $warehouseId,
        string $entryDate,
        int $referenceId,
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
            'entry_date' => $entryDate,
            'reference_type' => 'stock_entry',
            'reference_id' => $referenceId,
            'quantity_in' => $quantityIn,
            'quantity_out' => $quantityOut,
            'balance_qty' => $balanceQty,
            'basic_rate' => $basicRate,
            'stock_value' => $stockValue,
            'balance_value' => $balanceValue,
        ]);
    }

    private function createReverseLedger(
        int $companyId,
        StockEntry $entry,
        StockEntryItem $item,
        string $type
    ): void {
        if ($type === 'receipt_cancel') {
            $stock = WarehouseStock::where('company_id', $companyId)
                ->where('item_id', $item->item_id)
                ->where('warehouse_id', $item->target_warehouse_id)
                ->first();

            $this->createLedger(
                companyId: $companyId,
                itemId: $item->item_id,
                warehouseId: $item->target_warehouse_id,
                entryDate: now()->toDateString(),
                referenceId: $entry->id,
                quantityIn: 0,
                quantityOut: (float) $item->quantity,
                balanceQty: (float) ($stock?->quantity ?? 0),
                basicRate: (float) $item->basic_rate,
                stockValue: -((float) $item->outgoing_value ?: (float) $item->incoming_value),
                balanceValue: (float) ($stock?->stock_value ?? 0)
            );
        }

        if ($type === 'issue_cancel') {
            $stock = WarehouseStock::where('company_id', $companyId)
                ->where('item_id', $item->item_id)
                ->where('warehouse_id', $item->source_warehouse_id)
                ->first();

            $this->createLedger(
                companyId: $companyId,
                itemId: $item->item_id,
                warehouseId: $item->source_warehouse_id,
                entryDate: now()->toDateString(),
                referenceId: $entry->id,
                quantityIn: (float) $item->quantity,
                quantityOut: 0,
                balanceQty: (float) ($stock?->quantity ?? 0),
                basicRate: (float) $item->basic_rate,
                stockValue: (float) $item->outgoing_value,
                balanceValue: (float) ($stock?->stock_value ?? 0)
            );
        }

        if ($type === 'transfer_cancel') {
            $sourceStock = WarehouseStock::where('company_id', $companyId)
                ->where('item_id', $item->item_id)
                ->where('warehouse_id', $item->source_warehouse_id)
                ->first();

            $targetStock = WarehouseStock::where('company_id', $companyId)
                ->where('item_id', $item->item_id)
                ->where('warehouse_id', $item->target_warehouse_id)
                ->first();

            $this->createLedger(
                companyId: $companyId,
                itemId: $item->item_id,
                warehouseId: $item->target_warehouse_id,
                entryDate: now()->toDateString(),
                referenceId: $entry->id,
                quantityIn: 0,
                quantityOut: (float) $item->quantity,
                balanceQty: (float) ($targetStock?->quantity ?? 0),
                basicRate: (float) $item->basic_rate,
                stockValue: -((float) $item->outgoing_value),
                balanceValue: (float) ($targetStock?->stock_value ?? 0)
            );

            $this->createLedger(
                companyId: $companyId,
                itemId: $item->item_id,
                warehouseId: $item->source_warehouse_id,
                entryDate: now()->toDateString(),
                referenceId: $entry->id,
                quantityIn: (float) $item->quantity,
                quantityOut: 0,
                balanceQty: (float) ($sourceStock?->quantity ?? 0),
                basicRate: (float) $item->basic_rate,
                stockValue: (float) $item->incoming_value,
                balanceValue: (float) ($sourceStock?->stock_value ?? 0)
            );
        }
    }

    private function generateSeries(int $companyId): string
    {
        $lastId = StockEntry::query()
            ->where('company_id', $companyId)
            ->max('id') ?? 0;

        return 'STE-' . str_pad((string) ($lastId + 1), 5, '0', STR_PAD_LEFT);
    }
private function createMaterialReceiptReverseJournalEntry(int $companyId, StockEntry $entry): void
{
    $amount = (float) $entry->total_incoming_value;

    if ($amount <= 0) {
        return;
    }

    $settings = CompanyAccountSetting::query()
        ->where('company_id', $companyId)
        ->first();

    if (
        ! $settings ||
        ! $settings->default_inventory_account_id ||
        ! $settings->inventory_adjustment_account_id
    ) {
        throw new InvalidArgumentException(
            'Default inventory account and inventory adjustment account must be configured.'
        );
    }

    $journalEntry = JournalEntry::create([
        'company_id' => $companyId,
        'entry_number' => $this->generateJournalEntryNumber($companyId),
        'entry_date' => now()->toDateString(),
        'total_debit' => $amount,
        'total_credit' => $amount,
        'posted_at' => now(),
        'description' => 'Reverse journal entry for Material Receipt Cancel: ' . $entry->series,
        'status' => 'posted',
        'created_by' => auth('api')->id() ?? $entry->created_by,
    ]);

    $debitLine = JournalEntryLine::create([
        'company_id' => $companyId,
        'journal_entry_id' => $journalEntry->id,
        'account_id' => $settings->inventory_adjustment_account_id,
        'party_type' => null,
        'party_id' => null,
        'debit' => $amount,
        'credit' => 0,
        'note' => 'Dr Inventory Adjustment Account - Reverse Material Receipt',
    ]);

    $this->createGeneralLedgerEntry($journalEntry, $debitLine);

    $creditLine = JournalEntryLine::create([
        'company_id' => $companyId,
        'journal_entry_id' => $journalEntry->id,
        'account_id' => $settings->default_inventory_account_id,
        'party_type' => null,
        'party_id' => null,
        'debit' => 0,
        'credit' => $amount,
        'note' => 'Cr Inventory Account - Reverse Material Receipt',
    ]);

    $this->createGeneralLedgerEntry($journalEntry, $creditLine);
}
private function createMaterialIssueReverseJournalEntry(int $companyId, StockEntry $entry): void
{
    $amount = (float) $entry->total_outgoing_value;

    if ($amount <= 0) {
        return;
    }

    $settings = CompanyAccountSetting::query()
        ->where('company_id', $companyId)
        ->first();

    if (
        ! $settings ||
        ! $settings->default_inventory_account_id ||
        ! $settings->inventory_adjustment_account_id
    ) {
        throw new InvalidArgumentException(
            'Default inventory account and inventory adjustment account must be configured.'
        );
    }

    $journalEntry = JournalEntry::create([
        'company_id' => $companyId,
        'entry_number' => $this->generateJournalEntryNumber($companyId),
        'entry_date' => now()->toDateString(),
        'total_debit' => $amount,
        'total_credit' => $amount,
        'posted_at' => now(),
        'description' => 'Reverse journal entry for Material Issue Cancel: ' . $entry->series,
        'status' => 'posted',
        'created_by' => auth('api')->id() ?? $entry->created_by,
    ]);

    $debitLine = JournalEntryLine::create([
        'company_id' => $companyId,
        'journal_entry_id' => $journalEntry->id,
        'account_id' => $settings->default_inventory_account_id,
        'party_type' => null,
        'party_id' => null,
        'debit' => $amount,
        'credit' => 0,
        'note' => 'Dr Inventory Account - Reverse Material Issue',
    ]);

    $this->createGeneralLedgerEntry($journalEntry, $debitLine);

    $creditLine = JournalEntryLine::create([
        'company_id' => $companyId,
        'journal_entry_id' => $journalEntry->id,
        'account_id' => $settings->inventory_adjustment_account_id,
        'party_type' => null,
        'party_id' => null,
        'debit' => 0,
        'credit' => $amount,
        'note' => 'Cr Inventory Adjustment Account - Reverse Material Issue',
    ]);

    $this->createGeneralLedgerEntry($journalEntry, $creditLine);
}
    
}