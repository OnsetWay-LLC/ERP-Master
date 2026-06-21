<?php

namespace App\Http\Resources\MonthlyDistribution;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonthlyDistributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'fiscal_year' => $this->fiscal_year,
            'is_active' => $this->is_active,
            'lines' => $this->whenLoaded('lines', function () {
                return $this->lines->map(fn ($line) => [
                    'id' => $line->id,
                    'month' => $line->month,
                    'percentage' => (float) $line->percentage,
                ]);
            }),
        ];
    }
}