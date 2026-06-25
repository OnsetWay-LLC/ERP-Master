<?php

namespace App\Services\DiscountSetting;

use App\Models\Company;
use App\Models\DiscountSetting;
use RuntimeException;
class DiscountSettingService
{
    public function store(array $data): DiscountSetting
{
    $company = Company::firstOrFail();

    return DiscountSetting::updateOrCreate(
        [
            'company_id' => $company->id
        ],
        [
            'sub_accountant_max_discount'
                => $data['sub_accountant_max_discount'],

            'department_manager_max_discount'
                => $data['department_manager_max_discount'],

            'created_by' => auth('api')->id(),
        ]
    );
}
   public function show(): ?DiscountSetting
{
    $company = Company::query()->firstOrFail();

    return DiscountSetting::query()
        ->with(['company', 'creator'])
        ->where('company_id', $company->id)
        ->first();
}

    public function update(array $data): DiscountSetting
    {
        $company = Company::query()->firstOrFail();

        $setting = DiscountSetting::query()->firstOrCreate(
            ['company_id' => $company->id],
            [
                'sub_accountant_max_discount' => 0,
                'department_manager_max_discount' => 0,
                'created_by' => auth('api')->id(),
            ]
        );

        $setting->update([
            'sub_accountant_max_discount' => $data['sub_accountant_max_discount'],
            'department_manager_max_discount' => $data['department_manager_max_discount'],
            'created_by' => auth('api')->id(),
        ]);

        return $setting->fresh(['company', 'creator']);
    }
    public function delete(DiscountSetting $discountSetting): void
{
    $discountSetting->delete();
}
}