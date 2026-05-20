<?php

namespace App\Http\Resources\BankAccount;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,

            'account_name_ar' => $this->account_name_ar,
            'account_name_en' => $this->account_name_en,
            'bank_id' => $this->bank_id,
            'bank' => $this->whenLoaded('bank'),

            'iban' => $this->iban,
            'branch_code' => $this->branch_code,
            'bank_account_no' => $this->bank_account_no,
            'swift_code_bic' => $this->swift_code_bic,

            'account_id' => $this->account_id,
            'chart_account' => $this->whenLoaded('chartAccount'),

            'created_by' => $this->created_by,

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}