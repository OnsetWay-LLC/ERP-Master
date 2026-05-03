<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,

            'customer_type' => $this->customer_type,

            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,

            'email' => $this->email,
            'mobile_number' => $this->mobile_number,

            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'zip_code' => $this->zip_code,
            'state_province' => $this->state_province,
            'country' => $this->country,

            'company' => [
                'id' => $this->company?->id,
                'name_ar' => $this->company?->name_ar,
                'name_en' => $this->company?->name_en,
            ],

            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'username' => $this->creator?->username,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}