<?php

namespace App\Http\Resources\ChartOfAccount;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'parent_id' => $this->parent_id,

            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'account_number' => $this->account_number,

            'root_category' => $this->root_category,
            'sub_category' => $this->sub_category,
            'account_type' => $this->account_type,

            'is_active' => $this->is_active,
            'is_system' => $this->is_system,

            'children' => ChartOfAccountResource::collection(
                $this->whenLoaded('children')
            ),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}