<?php

namespace App\Http\Resources\SalesOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderFeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fees_template_id' => $this->fees_template_id,
            'title' => $this->title,
            'type' => $this->type,
            'account_id' => $this->account_id,
            'fees_rate' => $this->fees_rate,
            'amount' => $this->amount,
        ];
    }
}