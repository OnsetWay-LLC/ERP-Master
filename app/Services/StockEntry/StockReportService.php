<?php

namespace App\Services\StockEntry;

use App\Models\WarehouseStock;

class StockReportService
{
    public function stockBalance(array $filters)
    {
        $query = WarehouseStock::query()
            ->with([
                'item',
                'warehouse'
            ]);

        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->whereHas('item', function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('item_name_ar', 'like', "%{$search}%")
                  ->orWhere('item_name_en', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (!empty($filters['warehouse_type'])) {

            $query->whereHas('warehouse', function ($q) use ($filters) {

                if ($filters['warehouse_type'] === 'parent') {
                    $q->where('is_group', true);
                }

                if ($filters['warehouse_type'] === 'child') {
                    $q->where('is_group', false);
                }
            });
        }

        return $query
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10);
    }
}