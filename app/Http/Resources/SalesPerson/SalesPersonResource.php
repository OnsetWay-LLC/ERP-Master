<?php

namespace App\Http\Resources\SalesPerson;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesPersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'sales_person_name_ar' => $this->sales_person_name_ar,
            'sales_person_name_en' => $this->sales_person_name_en,

            'employee' => [
                'id' => $this->employee?->id,
                'series' => $this->employee?->series,
                'full_name_ar' => $this->employee?->full_name_ar,
                'full_name_en' => $this->employee?->full_name_en,
                'company_email' => $this->employee?->company_email,
            ],

            'user' => $this->user ? [
                'id' => $this->user?->id,
                'username' => $this->user?->username,
                'email' => $this->user?->email,
            ] : null,

            'commission_rate' => (float) $this->commission_rate,

            'use_default_account' => (bool) $this->use_default_account,

            'commission_account' => $this->commissionAccount ? [
                'id' => $this->commissionAccount->id,
                'name_ar' => $this->commissionAccount->name_ar,
                'name_en' => $this->commissionAccount->name_en,
                'account_number' => $this->commissionAccount->account_number,
                'account_type' => $this->commissionAccount->account_type,
            ] : null,

            'payroll_account' => $this->payrollAccount ? [
                'id' => $this->payrollAccount->id,
                'name_ar' => $this->payrollAccount->name_ar,
                'name_en' => $this->payrollAccount->name_en,
                'account_number' => $this->payrollAccount->account_number,
                'account_type' => $this->payrollAccount->account_type,
            ] : null,

            'is_active' => (bool) $this->is_active,

            'targets' => SalesPersonTargetResource::collection($this->whenLoaded('targets')),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'created_by' => $this->creator?->name,
        ];
    }
}