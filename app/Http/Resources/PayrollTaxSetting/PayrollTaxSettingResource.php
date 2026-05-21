<?php

namespace App\Http\Resources\PayrollTaxSetting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollTaxSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,

            'single_or_working_wife_exemption' => $this->single_or_working_wife_exemption,
            'married_not_working_wife_exemption' => $this->married_not_working_wife_exemption,

            'is_active' => $this->is_active,

            'brackets' => $this->whenLoaded('brackets', function () {
                return $this->brackets->map(fn ($bracket) => [
                    'id' => $bracket->id,
                    'from_amount' => $bracket->from_amount,
                    'to_amount' => $bracket->to_amount,
                    'rate' => $bracket->rate,
                    'sort_order' => $bracket->sort_order,
                ]);
            }),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}