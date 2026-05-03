<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Item::query()
            ->with('itemGroup.warehouse')
            ->get()
            ->map(function ($item) {
                return [
                    'item_code' => $item->item_code,
                    'name_ar' => $item->name_ar,
                    'name_en' => $item->name_en,
                    'barcode' => $item->barcode,
                    'selling_price' => $item->selling_price,
                    'purchase_price' => $item->purchase_price,
                    'currency_code' => $item->currency_code,
                    'status' => $item->status,
                    'item_group' => $item->itemGroup?->name_en,
                    'warehouse' => $item->itemGroup?->warehouse?->name_en,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Item Code',
            'Name AR',
            'Name EN',
            'Barcode',
            'Selling Price',
            'Purchase Price',
            'Currency',
            'Status',
            'Item Group',
            'Warehouse',
        ];
    }
}