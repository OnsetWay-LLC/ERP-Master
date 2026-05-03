<?php

namespace App\Http\Resources\Department;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'company' => [
                'id' => $this->company?->id,
                'name_ar' => $this->company?->name_ar,
                'name_en' => $this->company?->name_en,
            ],
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}