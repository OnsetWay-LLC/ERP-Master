<?php

namespace App\Services\PayrollTaxSetting;

use App\Models\Company;
use App\Models\PayrollTaxSetting;
use Illuminate\Support\Facades\DB;

class PayrollTaxSettingService
{
    public function getActive(): ?PayrollTaxSetting
    {
        $company = Company::query()->firstOrFail();

        return PayrollTaxSetting::query()
            ->with('brackets')
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    public function storeOrUpdate(array $data): PayrollTaxSetting
    {
        return DB::transaction(function () use ($data) {
            $company = Company::query()->firstOrFail();

            PayrollTaxSetting::query()
                ->where('company_id', $company->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $brackets = $data['brackets'];
            unset($data['brackets']);

            $setting = PayrollTaxSetting::create([
                ...$data,
                'company_id' => $company->id,
                'is_active' => true,
            ]);

            foreach ($brackets as $index => $bracket) {
                $setting->brackets()->create([
                    'from_amount' => $bracket['from_amount'],
                    'to_amount' => $bracket['to_amount'] ?? null,
                    'rate' => $bracket['rate'],
                    'sort_order' => $bracket['sort_order'] ?? ($index + 1),
                ]);
            }

            return $setting->fresh(['brackets']);
        });
    }
}