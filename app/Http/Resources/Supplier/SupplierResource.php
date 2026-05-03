<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supplier_type' => $this->supplier_type,

            'supplier_name_ar' => $this->supplier_name_ar,
            'supplier_name_en' => $this->supplier_name_en,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,

            'email' => $this->email,
            'mobile_number' => $this->mobile_number,

            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
            'state_province' => $this->state_province,
            'country' => $this->country,

            'created_by' => $this->creator?->name,
            'created_at' => $this->created_at,
        ];
    }
}