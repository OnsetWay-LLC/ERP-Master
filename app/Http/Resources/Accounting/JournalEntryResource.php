<?php

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'entry_number' => $this->entry_number,
            'entry_date' => $this->entry_date?->format('Y-m-d'),
            'description' => $this->description,
            'status' => $this->status,
            'total_debit' => $this->total_debit,
            'total_credit' => $this->total_credit,
            'prepared_by' => $this->creator?->name,
            'created_by' => $this->created_by,
            'posted_at' => $this->posted_at,
            'cancelled_at' => $this->cancelled_at,
            'reversed_entry_id' => $this->reversed_entry_id,

            'lines' => $this->lines->map(function ($line) {
                return [
                    'id' => $line->id,
                    'account_id' => $line->account_id,
                    'account_number' => $line->account?->account_number,
                    'account_name_ar' => $line->account?->name_ar,
                    'account_name_en' => $line->account?->name_en,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'note' => $line->note,
                ];
            }),
        ];
    }
}