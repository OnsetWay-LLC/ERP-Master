<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Services\ChartOfAccount\ChartOfAccountsSetupService;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->first();

        if (! $company) {
            $this->command?->warn('No company found. Chart of accounts seeder skipped.');
            return;
        }

        app(ChartOfAccountsSetupService::class)
            ->createForCompany($company->id);

        $this->command?->info('Chart of accounts created successfully.');
    }
}