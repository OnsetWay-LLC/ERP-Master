<?php

namespace App\Services\Reports;

use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;

class WarehouseWiseStockBalanceReportService
{
    public function generate(array $filters): array
    {
        $locale = app()->getLocale();

        $query = StockLedger::query()
            ->join('warehouses', 'warehouses.id', '=', 'stock_ledger.warehouse_id')
            ->join('items', 'items.id', '=', 'stock_ledger.item_id')
            ->leftJoin('sales_people', 'sales_people.id', '=', 'warehouses.sales_person_id')
            ->leftJoin('employees', 'employees.id', '=', 'sales_people.employee_id')
            ->select([
                'warehouses.id as warehouse_id',
                'warehouses.name_ar as warehouse_name_ar',
                'warehouses.name_en as warehouse_name_en',
                'warehouses.is_group',
                'items.id as item_id',
                'items.name_ar as item_name_ar',
                'items.name_en as item_name_en',

                DB::raw("COALESCE(employees.full_name_en, employees.full_name_ar, '-') as sales_person_name"),

                DB::raw('SUM(CAST(stock_ledger.quantity_in AS DECIMAL(18,2))) as in_qty'),
                DB::raw('SUM(CAST(stock_ledger.quantity_out AS DECIMAL(18,2))) as out_qty'),
                DB::raw('SUM(CAST(stock_ledger.quantity_in AS DECIMAL(18,2))) - SUM(CAST(stock_ledger.quantity_out AS DECIMAL(18,2))) as balance_qty'),
                DB::raw('MAX(CAST(stock_ledger.basic_rate AS DECIMAL(18,2))) as valuation_rate'),
            ])
            ->groupBy([
                'warehouses.id',
                'warehouses.name_ar',
                'warehouses.name_en',
                'warehouses.is_group',
                'items.id',
                'items.name_ar',
                'items.name_en',
                'employees.full_name_en',
                'employees.full_name_ar',
            ]);

        if (! empty($filters['warehouse_id'])) {
            $query->where('stock_ledger.warehouse_id', $filters['warehouse_id']);
        }

        if (! empty($filters['item_id'])) {
            $query->where('stock_ledger.item_id', $filters['item_id']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('stock_ledger.entry_date', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('stock_ledger.entry_date', '<=', $filters['to_date']);
        }

        $rows = $query
            ->orderBy('warehouses.name_en')
            ->orderBy('items.name_en')
            ->get()
            ->map(function ($row) use ($locale) {
                $warehouseName = $locale === 'ar'
                    ? ($row->warehouse_name_ar ?? $row->warehouse_name_en)
                    : ($row->warehouse_name_en ?? $row->warehouse_name_ar);

                $itemName = $locale === 'ar'
                    ? ($row->item_name_ar ?? $row->item_name_en)
                    : ($row->item_name_en ?? $row->item_name_ar);

                $balanceQty = (float) $row->balance_qty;
                $valuationRate = (float) $row->valuation_rate;

                return [
                    'warehouse' => $warehouseName,
                    'warehouse_type' => (bool) $row->is_group ? 'Main Warehouse' : 'Child Warehouse',
                    'sales_person' => (bool) $row->is_group ? '-' : ($row->sales_person_name ?? '-'),
                    'item_name' => $itemName,
                    'in_qty' => round((float) $row->in_qty, 2),
                    'out_qty' => round((float) $row->out_qty, 2),
                    'balance_qty' => round($balanceQty, 2),
                    'valuation_rate' => round($valuationRate, 2),
                    'stock_value' => round($balanceQty * $valuationRate, 2),
                ];
            })
            ->values();

        return [
            'rows' => $rows->toArray(),
            'totals' => [
                'in_qty' => round($rows->sum('in_qty'), 2),
                'out_qty' => round($rows->sum('out_qty'), 2),
                'balance_qty' => round($rows->sum('balance_qty'), 2),
                'stock_value' => round($rows->sum('stock_value'), 2),
            ],
        ];
    }
}