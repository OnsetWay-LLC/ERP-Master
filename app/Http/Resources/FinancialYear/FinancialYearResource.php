<?php

namespace App\Http\Resources\FinancialYear;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialYearResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,

            'year_name' => $this->year_name,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'closing_date' => $this->closing_date?->format('Y-m-d'),

            'status' => $this->status,
            'grace_period_end' => $this->grace_period_end?->format('Y-m-d'),

            'closed_at' => $this->closed_at?->format('Y-m-d H:i:s'),
            'closed_by' => $this->closed_by,
            'created_by' => $this->created_by,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}