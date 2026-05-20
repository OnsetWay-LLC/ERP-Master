<?php

namespace App\Http\Resources\FeesTemplate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeesTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,

            'title' => $this->title,
            'type' => $this->type,

            'account_id' => $this->account_id,
            'account' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'name_ar' => $this->account->name_ar,
                    'name_en' => $this->account->name_en,
                    'account_number' => $this->account->account_number,
                    'account_type' => $this->account->account_type,
                    'account_level' => $this->account->account_level,
                ];
            }),

            'fees_rate' => $this->fees_rate,
            'amount' => $this->amount,
            'is_active' => $this->is_active,

            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}