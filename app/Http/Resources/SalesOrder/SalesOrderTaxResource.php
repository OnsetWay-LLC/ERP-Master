<?php

namespace App\Http\Resources\SalesOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderTaxResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tax_template_id' => $this->tax_template_id,
            'tax_template_line_id' => $this->tax_template_line_id,
            'title' => $this->title,
            'type' => $this->type,
            'account_id' => $this->account_id,
            'tax_rate' => $this->tax_rate,
            'amount' => $this->amount,
        ];
    }
}