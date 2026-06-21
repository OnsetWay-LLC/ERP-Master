<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            
            'username' => $this->username,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'roles' => $this->getRoleNames()->values(),
            'employee' => [
                'id' => $this->employee?->id,
                'national_id' => $this->employee?->national_id,
                'full_name_ar' => $this->employee?->full_name_ar,
                'full_name_en' => $this->employee?->full_name_en,
                'department' => [
                    'id' => $this->employee?->department?->id,
                    'name_ar' => $this->employee?->department?->name_ar,
                    'name_en' => $this->employee?->department?->name_en,
                ],
            ],
            'created_at' => $this->created_at,
        ];
    }
}