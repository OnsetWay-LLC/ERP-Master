<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'is_active' => $this->is_active,
            'is_initial_admin' => $this->is_initial_admin,
            'employee' => $this->when($this->employee_id !== null, [
                'id' => $this->employee?->id,
                'full_name' => $this->employee?->full_name,
                'mobile_number' => $this->employee?->mobile_number,
                'company_email' => $this->employee?->company_email,
            ]),
            'roles' => $this->getRoleNames()->values(),
            'created_at' => $this->created_at,
        ];
    }
}