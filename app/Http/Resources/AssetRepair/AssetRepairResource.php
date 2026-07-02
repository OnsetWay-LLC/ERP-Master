<?php

namespace App\Http\Resources\AssetRepair;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetRepairResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'series' => $this->series,

            'asset_id' => $this->asset_id,
            'asset_series' => $this->asset?->series,
            'asset_name_ar' => $this->asset?->asset_name_ar,
            'asset_name_en' => $this->asset?->asset_name_en,
            'asset' => $this->whenLoaded('asset'),

            'repair_status' => $this->repair_status,
            'failure_date' => $this->failure_date?->format('Y-m-d'),
            'completed_date' => $this->completed_date?->format('Y-m-d H:i:s'),

            'error_description' => $this->error_description,
            'actions_performed' => $this->actions_performed,

            'repair_cost_total' => (float) $this->repair_cost_total,
            'status' => $this->status,

            'items' => $this->whenLoaded('items'),

            'created_by' => $this->created_by,
            'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
            'submitted_by' => $this->submitted_by,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
