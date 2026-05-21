<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'series' => $this->series,

            'full_name_ar' => $this->full_name_ar,
            'full_name_en' => $this->full_name_en,
            'national_id' => $this->national_id,

            'gender' => $this->gender,
            'status' => $this->status,
            'date_of_joining' => $this->date_of_joining?->format('Y-m-d'),

            'job_title' => $this->job_title,

            'marital_status' => $this->marital_status,
            'wife_working_status' => $this->wife_working_status,

            'mobile_number' => $this->mobile_number,
            'company_email' => $this->company_email,
            'address' => $this->address,

            'company' => $this->whenLoaded('company', fn () => [
                'id' => $this->company->id,
                'name' => $locale === 'ar'
                    ? $this->company->name_ar
                    : $this->company->name_en,
                'name_ar' => $this->company->name_ar,
                'name_en' => $this->company->name_en,
            ]),

            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department?->id,
                'name' => $locale === 'ar'
                    ? $this->department?->name_ar
                    : $this->department?->name_en,
                'name_ar' => $this->department?->name_ar,
                'name_en' => $this->department?->name_en,
            ]),

            'salary' => $this->whenLoaded('activeSalary', fn () => [
                'salary_mode' => $this->activeSalary?->salary_mode,
                'salary_value' => $this->activeSalary?->salary_value,
                'bank_account_name' => $this->activeSalary?->bank_account_name,
                'bank_account_number' => $this->activeSalary?->bank_account_number,
                'iban' => $this->activeSalary?->iban,
                'social_security_deduction' => $this->activeSalary?->social_security_deduction,
                'insurance_deduction' => $this->activeSalary?->insurance_deduction,
                'tax_deduction' => $this->activeSalary?->tax_deduction,
                'effective_from' => $this->activeSalary?->effective_from?->format('Y-m-d'),
            ]),

            'allowances' => $this->whenLoaded('allowances', function () use ($locale) {
                return $this->allowances->map(fn ($allowance) => [
                    'id' => $allowance->id,
                    'name' => $locale === 'ar'
                        ? $allowance->name_ar
                        : $allowance->name_en,
                    'name_ar' => $allowance->name_ar,
                    'name_en' => $allowance->name_en,
                    'amount' => $allowance->amount,
                ]);
            }),

            'shifts' => $this->whenLoaded('shifts', function () {
                return $this->shifts->map(fn ($shift) => [
                    'id' => $shift->id,
                    'name_ar' => $shift->name_ar,
                    'name_en' => $shift->name_en,
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                    'is_default' => (bool) $shift->pivot?->is_default,
                ]);
            }),

            'educations' => $this->whenLoaded('educations', function () {
                return $this->educations->map(fn ($education) => [
                    'id' => $education->id,
                    'school_university_ar' => $education->school_university_ar,
                    'school_university_en' => $education->school_university_en,
                    'qualification_ar' => $education->qualification_ar,
                    'qualification_en' => $education->qualification_en,
                    'level' => $education->level,
                    'year_of_passing' => $education->year_of_passing,
                    'class_percentage' => $education->class_percentage,
                    'major_ar' => $education->major_ar,
                    'major_en' => $education->major_en,
                ]);
            }),

           'leave_balances' => $this->whenLoaded('leaveBalances', function () use ($locale) {
    return $this->leaveBalances->map(fn ($balance) => [
        'id' => $balance->id,
        'leave_type' => $balance->leave_type,
        'name' => $locale === 'ar'
            ? $balance->name_ar
            : $balance->name_en,
        'name_ar' => $balance->name_ar,
        'name_en' => $balance->name_en,
        'total_days' => $balance->total_days,
        'used_days' => $balance->used_days,
        'remaining_days' => $balance->remaining_days,
    ]);
}),
        ];
    
    }}
