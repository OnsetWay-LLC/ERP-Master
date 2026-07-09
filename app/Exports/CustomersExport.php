<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Customer::query()->latest('id')->get();
    }

    public function headings(): array
    {
        return [
            'customer_type',
            'name_ar',
            'name_en',
            'email',
            'mobile_number',
            'address_line_1',
            'address_line_2',
            'city',
            'zip_code',
            'state_province',
            'country',
            'opening_balance',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->customer_type,
            $customer->name_ar,
            $customer->name_en,
            $customer->email,
            $customer->mobile_number,
            $customer->address_line_1,
            $customer->address_line_2,
            $customer->city,
            $customer->zip_code,
            $customer->state_province,
            $customer->country,
            $customer->opening_balance,
        ];
    }
}