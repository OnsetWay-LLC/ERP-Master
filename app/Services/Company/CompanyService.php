<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Services\ChartOfAccount\ChartOfAccountsSetupService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function __construct(
        private readonly ChartOfAccountsSetupService $chartOfAccountsSetupService
    ) {}

    public function getAll(): Collection
    {
        return Company::query()
            ->withCount('departments')
            ->latest('id')
            ->get();
    }

    public function getById(int $id): Company
    {
        return Company::query()
            ->withCount('departments')
            ->findOrFail($id);
    }

    public function create(array $data): Company
    {
        if (Company::query()->exists()) {
            abort(422, 'A company already exists. Only one company is allowed.');
        }

        $country = $data['country'];

        $countries = config('company.countries');
        $currency = $countries[$country]['currency'] ?? null;

        if (! $currency) {
            abort(422, 'Invalid country selected.');
        }

        $data['currency_code'] = $currency;

        return DB::transaction(function () use ($data) {
            $company = Company::create($data);

            $this->chartOfAccountsSetupService
                ->createForCompany($company->id);

            return $company->fresh()->loadCount('departments');
        });
    }

    public function update(Company $company, array $data): Company
    {
        if (isset($data['country'])) {
            $country = $data['country'];

            $countries = config('company.countries');
            $currency = $countries[$country]['currency'] ?? null;

            if (! $currency) {
                abort(422, 'Invalid country selected.');
            }

            $data['currency_code'] = $currency;
        }

        $company->update($data);

        return $company->fresh()->loadCount('departments');
    }
}