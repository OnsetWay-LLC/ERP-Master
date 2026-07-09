<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SuppliersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Supplier::query()->latest()->get();
    }

    public function headings(): array
    {
        return [
            'supplier_type',
            'supplier_name_ar',
            'supplier_name_en',
            'first_name',
            'last_name',
            'email',
            'mobile_number',
            'address_line_1',
            'address_line_2',
            'zip_code',
            'city',
            'state_province',
            'country',
            'opening_balance',
        ];
    }

    public function map($supplier): array
    {
        return [
            $supplier->supplier_type,
            $supplier->supplier_name_ar,
            $supplier->supplier_name_en,
            $supplier->first_name,
            $supplier->last_name,
            $supplier->email,
            $supplier->mobile_number,
            $supplier->address_line_1,
            $supplier->address_line_2,
            $supplier->zip_code,
            $supplier->city,
            $supplier->state_province,
            $supplier->country,
            $supplier->opening_balance,
        ];
    }
}