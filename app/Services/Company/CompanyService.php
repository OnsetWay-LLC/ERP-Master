<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Services\ChartOfAccount\ChartOfAccountsSetupService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    private array $defaultDays = [
        'saturday',
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ];

    public function __construct(
        private readonly ChartOfAccountsSetupService $chartOfAccountsSetupService
    ) {}

    public function getAll(): Collection
    {
        return Company::query()
            ->with(['workingDays'])
            ->withCount('departments')
            ->latest('id')
            ->get();
    }

    public function getById(int $id): Company
    {
        return Company::query()
            ->with(['workingDays'])
            ->withCount('departments')
            ->findOrFail($id);
    }

    public function create(array $data): Company
    {
        if (Company::query()->exists()) {
            abort(422, 'A company already exists. Only one company is allowed.');
        }

        $workingDays = $data['working_days'] ?? null;
        unset($data['working_days']);

        $data['currency_code'] = $this->getCurrencyByCountry($data['country']);

        return DB::transaction(function () use ($data, $workingDays) {
            $company = Company::create($data);

            $this->syncWorkingDays($company, $workingDays);

            $this->chartOfAccountsSetupService
                ->createForCompany($company->id);

            return $company->fresh(['workingDays'])->loadCount('departments');
        });
    }

    public function update(Company $company, array $data): Company
    {
        $workingDays = $data['working_days'] ?? null;
        unset($data['working_days']);

        if (isset($data['country'])) {
            $data['currency_code'] = $this->getCurrencyByCountry($data['country']);
        }

        return DB::transaction(function () use ($company, $data, $workingDays) {
            $company->update($data);

            if ($workingDays !== null) {
                $this->syncWorkingDays($company, $workingDays);
            }

            return $company->fresh(['workingDays'])->loadCount('departments');
        });
    }

    private function getCurrencyByCountry(string $country): string
    {
        $countries = config('company.countries');

        $currency = $countries[$country]['currency'] ?? null;

        if (! $currency) {
            abort(422, 'Invalid country selected.');
        }

        return $currency;
    }

    private function syncWorkingDays(Company $company, ?array $workingDays): void
    {
        $workingDays = $workingDays ?: $this->defaultWorkingDays();

        foreach ($workingDays as $workingDay) {
            $company->workingDays()->updateOrCreate(
                [
                    'day' => $workingDay['day'],
                ],
                [
                    'is_working_day' => $workingDay['is_working_day'],
                ]
            );
        }
    }

    private function defaultWorkingDays(): array
    {
        return collect($this->defaultDays)
            ->map(fn ($day) => [
                'day' => $day,
                'is_working_day' => $day !== 'friday',
            ])
            ->toArray();
    }
}