<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Item;
use App\Models\ItemGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsImport implements ToCollection, WithHeadingRow
{
    protected int $rowCount = 0;

    public function collection(Collection $rows): void
    {
        $company = Company::query()->firstOrFail();

        foreach ($rows as $row) {
            $data = [
                'item_group_id' => $row['item_group_id'] ?? null,
                'item_code' => $row['item_code'] ?? null,
                'name_ar' => $row['name_ar'] ?? null,
                'name_en' => $row['name_en'] ?? null,
                'barcode' => $row['barcode'] ?? null,
                'selling_price' => $row['selling_price'] ?? 0,
                'purchase_price' => $row['purchase_price'] ?? 0,
                'currency_code' => $row['currency_code'] ?? $company->currency_code,
                'status' => $row['status'] ?? 'active',
            ];

            Validator::make($data, [
                'item_group_id' => ['required', 'exists:item_groups,id'],
                'item_code' => ['required', 'string'],
                'name_ar' => ['required', 'string'],
                'name_en' => ['required', 'string'],
                'barcode' => ['nullable', 'string'],
                'selling_price' => ['required', 'numeric', 'min:0'],
                'purchase_price' => ['required', 'numeric', 'min:0'],
                'currency_code' => ['required', 'string', 'max:10'],
                'status' => ['required', 'in:active,inactive'],
            ])->validate();

            $itemGroup = ItemGroup::query()
                ->where('company_id', $company->id)
                ->findOrFail($data['item_group_id']);

            Item::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'item_code' => $data['item_code'],
                ],
                [
                    'item_group_id' => $itemGroup->id,
                    'name_ar' => $data['name_ar'],
                    'name_en' => $data['name_en'],
                    'barcode' => $data['barcode'],
                    'selling_price' => $data['selling_price'],
                    'purchase_price' => $data['purchase_price'],
                    'currency_code' => $data['currency_code'],
                    'status' => $data['status'],
                    'created_by' => auth('api')->id(),
                ]
            );

            $this->rowCount++;
        }
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}