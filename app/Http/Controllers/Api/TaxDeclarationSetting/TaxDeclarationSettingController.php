<?php

namespace App\Http\Controllers\Api\TaxDeclarationSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaxDeclarationSetting\StoreTaxDeclarationSettingRequest;
use App\Http\Requests\TaxDeclarationSetting\UpdateTaxDeclarationSettingRequest;
use App\Models\Company;
use App\Models\TaxDeclarationSetting;

class TaxDeclarationSettingController extends Controller
{
    public function show()
    {
        $company = Company::query()->firstOrFail();

        $setting = TaxDeclarationSetting::with(['creator', 'updater'])
            ->firstOrCreate(
                ['company_id' => $company->id],
                [
                    'report_month_type' => 'odd',
                    'created_by' => auth('api')->id(),
                    'updated_by' => auth('api')->id(),
                ]
            );

        $setting->load(['creator', 'updater']);

        return response()->json([
            'status' => true,
            'data' => $setting,
        ]);
    }

    public function store(StoreTaxDeclarationSettingRequest $request)
{
    $company = Company::query()->firstOrFail();

    $setting = TaxDeclarationSetting::firstOrNew([
        'company_id' => $company->id,
    ]);

    if ($setting->exists) {
        return response()->json([
            'status' => false,
            'message' => 'Tax declaration setting already exists.',
        ], 422);
    }

    $setting->report_month_type = $request->report_month_type;
    $setting->created_by = auth('api')->id();
    $setting->updated_by = auth('api')->id();

    $setting->save();

    $setting->load(['creator', 'updater']);

    return response()->json([
        'status' => true,
        'message' => 'Tax declaration setting saved successfully.',
        'data' => $setting,
    ]);
}
   public function update(UpdateTaxDeclarationSettingRequest $request)
{
    $company = Company::query()->firstOrFail();

    $setting = TaxDeclarationSetting::firstOrNew([
        'company_id' => $company->id,
    ]);

    if (! $setting->exists) {
        $setting->created_by = auth('api')->id();
    }

    $setting->report_month_type = $request->report_month_type;
    $setting->updated_by = auth('api')->id();

    $setting->save();

    $setting->load(['creator', 'updater']);

    return response()->json([
        'status' => true,
        'message' => 'Tax declaration setting updated successfully.',
        'data' => $setting,
    ]);
}
}