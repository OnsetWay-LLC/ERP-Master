<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'series' => $this->series,

            'full_name' => $this->full_name,
            'national_id' => $this->national_id,
            'gender' => $this->gender,
            'status' => $this->status,

'date_of_joining' => $this->date_of_joining?->format('Y-m-d'),

            'marital_status' => $this->marital_status,
'mobile_number' => $this->mobile_number,
            'address' => $this->address,
'company_email' => $this->company_email,
            'company' => $this->company?->name_en,
            'department' => $this->department?->name_en,

            'shifts' => $this->shifts->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                    'is_default' => (bool) $shift->pivot?->is_default,
                ];
            }),

            'educations' => $this->educations,

            'salary_mode' => $this->salary_mode,
            'salary_value' => $this->salary_value,
        ];
    }
}