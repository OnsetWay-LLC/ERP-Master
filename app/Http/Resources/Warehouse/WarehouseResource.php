<?php

namespace App\Http\Resources\Warehouse;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,

            'is_group' => (bool) $this->is_group,
            'type' => $this->is_group ? 'parent' : 'child',

            'parent' => $this->parent ? [
                'id' => $this->parent->id,
                'name_ar' => $this->parent->name_ar,
                'name_en' => $this->parent->name_en,
            ] : null,

            'sales_person' => $this->salesPerson ? [
                'id' => $this->salesPerson->id,
                'name_ar' => $this->salesPerson->sales_person_name_ar ?? null,
                'name_en' => $this->salesPerson->sales_person_name_en ?? null,
            ] : null,

            'opening_balance' => $this->opening_balance,

            'created_by' => $this->creator?->name,
            'created_at' => $this->created_at,
        ];
    }
}