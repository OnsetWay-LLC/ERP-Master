<?php

namespace App\Http\Resources\ItemGroup;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,

          

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
            'deleted_at' => $this->deleted_at,
        ];
    }
}