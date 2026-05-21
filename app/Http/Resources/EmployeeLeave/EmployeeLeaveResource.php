<?php

namespace App\Http\Resources\EmployeeLeave;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLeaveResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'series' => $this->employee->series,
                'full_name_ar' => $this->employee->full_name_ar,
                'full_name_en' => $this->employee->full_name_en,
                'company_email' => $this->employee->company_email,
            ]),

            'leave_type' => $this->leave_type,
            'from_date' => $this->from_date?->format('Y-m-d'),
            'to_date' => $this->to_date?->format('Y-m-d'),
            'days_count' => $this->days_count,

            'description' => $this->description,
            'status' => $this->status,

            'deduct_from_salary' => $this->deduct_from_salary,
            'salary_deduction_amount' => $this->salary_deduction_amount,

            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i'),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}