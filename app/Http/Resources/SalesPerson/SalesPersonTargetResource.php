<?php

namespace App\Http\Resources\SalesPerson;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesPersonTargetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'item_group' => [
                'id' => $this->itemGroup?->id,
                'name_ar' => $this->itemGroup?->name_ar,
                'name_en' => $this->itemGroup?->name_en,
            ],

            'monthly_distribution' => [
                'id' => $this->monthlyDistribution?->id,
                'title' => $this->monthlyDistribution?->title,
            ],

            'target_amount' => (float) $this->target_amount,
        ];
    }
}