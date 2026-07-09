<?php

namespace App\Imports;

use App\Services\Customer\CustomerService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class CustomersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['name_en']) && empty($row['name_ar'])) {
                continue;
            }

            $this->customerService->create([
                'customer_type' => $row['customer_type'] ?? 'individual',
                'name_ar' => $row['name_ar'] ?? null,
                'name_en' => $row['name_en'] ?? null,
                'email' => $row['email'] ?? null,
                'mobile_number' => $row['mobile_number'] ?? null,
                'address_line_1' => $row['address_line_1'] ?? null,
                'address_line_2' => $row['address_line_2'] ?? null,
                'city' => $row['city'] ?? null,
                'zip_code' => $row['zip_code'] ?? null,
                'state_province' => $row['state_province'] ?? null,
                'country' => $row['country'] ?? null,
                'opening_balance' => (float) ($row['opening_balance'] ?? 0),
            ]);
        }
    }
}