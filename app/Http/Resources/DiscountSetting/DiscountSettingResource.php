<?php

namespace App\Http\Resources\DiscountSetting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'company' => [
                'id' => $this->company?->id,
                'name_ar' => $this->company?->name_ar,
                'name_en' => $this->company?->name_en,
            ],

            'sub_accountant_max_discount' => (float) $this->sub_accountant_max_discount,
            'department_manager_max_discount' => (float) $this->department_manager_max_discount,

            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator?->id,
                    'name' => $this->creator?->name,
                    'email' => $this->creator?->email,
                ];
            }),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
        ];
    }
}