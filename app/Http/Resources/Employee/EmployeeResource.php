<?php
namespace App\Http\Resources\Employee;
use Illuminate\Http\Request;
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
            

            'company' => $this->company?->name_en,
            'department' => $this->department?->name_en,

            'shifts' => $this->shifts,
            'educations' => $this->educations,
            'salary_mode' => $this->salary_mode,
            'salary_value' => $this->salary_value
        ];
    }
}