<?php

namespace App\Imports;

use App\Services\Supplier\SupplierService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SuppliersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function __construct(
        private readonly SupplierService $supplierService
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['supplier_name_en']) && empty($row['supplier_name_ar'])) {
                continue;
            }

            $this->supplierService->create([
                'supplier_type' => $row['supplier_type'] ?? 'company',

                'supplier_name_ar' => $row['supplier_name_ar'] ?? null,
                'supplier_name_en' => $row['supplier_name_en'] ?? null,

                'first_name' => $row['first_name'] ?? null,
                'last_name' => $row['last_name'] ?? null,

                'email' => $row['email'] ?? null,
                'mobile_number' => $row['mobile_number'] ?? null,

                'address_line_1' => $row['address_line_1'] ?? null,
                'address_line_2' => $row['address_line_2'] ?? null,
                'zip_code' => $row['zip_code'] ?? null,

                'city' => $row['city'] ?? null,
                'state_province' => $row['state_province'] ?? null,
                'country' => $row['country'] ?? null,

                'opening_balance' => (float) ($row['opening_balance'] ?? 0),
            ]);
        }
    }
}