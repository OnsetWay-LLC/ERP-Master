<?php

namespace App\Services\Item;

use App\Models\Company;
use App\Models\Item;
use App\Exports\ItemsExport;
use App\Imports\ItemsImport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ItemService
{
    public function getAll(array $filters): LengthAwarePaginator
    {
        $query = Item::query()
            ->with(['company', 'itemGroup.warehouse', 'creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhereHas('itemGroup', function ($groupQuery) use ($search) {
                        $groupQuery->where('name_ar', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['item_group_id'])) {
            $query->where('item_group_id', $filters['item_group_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $warehouseId = $filters['warehouse_id'];

            $query->whereHas('itemGroup', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        if (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        return $query
            ->latest('id')
            ->paginate($filters['per_page'] ?? 10)
            ->appends($filters);
    }

    public function getById(int $id): Item
    {
        return Item::query()
            ->with(['company', 'itemGroup.warehouse', 'creator'])
            ->findOrFail($id);
    }

    public function create(array $data): Item
    {
        $company = Company::query()->firstOrFail();

        $data['company_id'] = $company->id;
        $data['created_by'] = auth('api')->id();

        return Item::create($data)->load(['company', 'itemGroup.warehouse', 'creator']);
    }

    public function update(Item $item, array $data): Item
    {
        $item->update($data);

        return $item->fresh()->load(['company', 'itemGroup.warehouse', 'creator']);
    }

    public function delete(Item $item): void
    {
        $item->delete();
    }
   public function scanBarcode(string $barcode): ?Item
{
    return Item::query()
        ->with(['company', 'itemGroup.warehouse', 'creator'])
        ->where('barcode', $barcode)
        ->first();
}

public function exportExcel()
{
    return Excel::download(new ItemsExport, 'items.xlsx');
}

public function importExcel($file): array
{
    $import = new ItemsImport();

    Excel::import($import, $file);

    return [
        'imported' => $import->getRowCount(),
    ];
}

public function printBarcode(int $id)
{
    $item = Item::query()
        ->with(['itemGroup.warehouse'])
        ->findOrFail($id);

    if (empty($item->barcode)) {
        abort(422, 'This item does not have a barcode.');
    }

    $generator = new BarcodeGeneratorPNG();

    $barcode = $generator->getBarcode(
        $item->barcode,
        $generator::TYPE_CODE_128,
        2,
        60
    );

    $barcodeImg = 'data:image/png;base64,' . base64_encode($barcode);

    $pdf = Pdf::loadView('pdf.barcode', [
        'item' => $item,
        'barcodeImg' => $barcodeImg,
    ])->setPaper([0, 0, 198, 95]);

    return $pdf->download("barcode-{$item->barcode}.pdf");
}
}