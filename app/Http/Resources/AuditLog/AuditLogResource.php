<?php

namespace App\Http\Resources\AuditLog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'operation' => $this->operation,
            'date_time' => $this->performed_at?->format('Y-m-d H:i:s'),
        ];
    }
}