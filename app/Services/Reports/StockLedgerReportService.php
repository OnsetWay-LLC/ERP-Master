<?php

namespace App\Services\Reports;

use App\Models\StockLedger;

class StockLedgerReportService
{
    public function generate(array $filters): array
    {
        $locale = app()->getLocale();

        $query = StockLedger::query()
            ->with([
                'item:id,item_code,name_ar,name_en',
                'warehouse:id,name_ar,name_en,name',
            ])
            ->when($filters['from_date'] ?? null, function ($q, $date) {
                $q->whereDate('entry_date', '>=', $date);
            })
            ->when($filters['to_date'] ?? null, function ($q, $date) {
                $q->whereDate('entry_date', '<=', $date);
            })
            ->when($filters['item_id'] ?? null, function ($q, $itemId) {
                $q->where('item_id', $itemId);
            })
            ->when($filters['warehouse_id'] ?? null, function ($q, $warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            })
            ->when($filters['transaction_type'] ?? null, function ($q, $type) {
                $q->where('reference_type', $type);
            })
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        $paginated = $query->paginate($filters['per_page'] ?? 15);

        return [
            'status' => true,
            'message' => $paginated->total() > 0
                ? 'Stock Ledger Report loaded successfully.'
                : 'No Stock Ledger Records Found.',
            'data' => $paginated->through(function ($row) use ($locale) {
                return [
                    'date' => optional($row->entry_date)->format('Y-m-d') ?? $row->entry_date,
                    'item_code' => $row->item?->item_code,
                    'item_name' => $locale === 'ar'
                        ? ($row->item?->name_ar ?? $row->item?->name_en)
                        : ($row->item?->name_en ?? $row->item?->name_ar),
                    'warehouse' => $row->warehouse?->name
                        ?? ($locale === 'ar'
                            ? ($row->warehouse?->name_ar ?? $row->warehouse?->name_en)
                            : ($row->warehouse?->name_en ?? $row->warehouse?->name_ar)),
                    'transaction_type' => $row->reference_type,
                    'reference_id' => $row->reference_id,
                    'in_qty' => (float) $row->quantity_in,
                    'out_qty' => (float) $row->quantity_out,
                    'balance_qty' => (float) $row->balance_qty,
                ];
            }),
        ];
    }
    public function generateForPdf(array $filters): array
{
    $locale = app()->getLocale();

    $rows = StockLedger::query()
        ->with([
            'item:id,item_code,name_ar,name_en',
            'warehouse:id,name_ar,name_en',
        ])
        ->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('entry_date', '>=', $date))
        ->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('entry_date', '<=', $date))
        ->when($filters['item_id'] ?? null, fn ($q, $id) => $q->where('item_id', $id))
        ->when($filters['warehouse_id'] ?? null, fn ($q, $id) => $q->where('warehouse_id', $id))
        ->orderByDesc('entry_date')
        ->orderByDesc('id')
        ->get()
        ->map(function ($row) use ($locale) {
            return [
                'date' => optional($row->entry_date)->format('Y-m-d') ?? $row->entry_date,
                'item_code' => $row->item?->item_code,
                'item_name' => $locale === 'ar'
                    ? ($row->item?->name_ar ?? $row->item?->name_en)
                    : ($row->item?->name_en ?? $row->item?->name_ar),
                'warehouse' => $locale === 'ar'
    ? ($row->warehouse?->name_ar ?? $row->warehouse?->name_en)
    : ($row->warehouse?->name_en ?? $row->warehouse?->name_ar),
                'transaction_type' => $row->reference_type,
                'in_qty' => (float) $row->quantity_in,
                'out_qty' => (float) $row->quantity_out,
                'balance_qty' => (float) $row->balance_qty,
            ];
        });

    return [
        'rows' => $rows,
        'filters' => $filters,
    ];
}
}