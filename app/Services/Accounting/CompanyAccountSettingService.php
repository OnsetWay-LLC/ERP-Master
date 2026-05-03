<?php
namespace App\Services\Accounting;

use App\Models\CompanyAccountSetting;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CompanyAccountSettingService
{
    public function set(array $data, int $companyId)
    {
        return DB::transaction(function () use ($data, $companyId) {

            // 🔥 تحقق إن الحسابات صحيحة
            foreach ($data as $key => $accountId) {

                if (!$accountId) continue;

                $account = ChartOfAccount::where('company_id', $companyId)
                    ->find($accountId);

                if (!$account) {
                    throw new InvalidArgumentException("Account not found for $key");
                }
            }

            return CompanyAccountSetting::updateOrCreate(
                ['company_id' => $companyId],
                $data
            );
        });
    }

    public function get(int $companyId)
    {
        return CompanyAccountSetting::where('company_id', $companyId)->first();
    }
   
}