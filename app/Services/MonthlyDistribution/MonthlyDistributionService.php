<?php

namespace App\Services\MonthlyDistribution;

use App\Models\Company;
use App\Models\MonthlyDistribution;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MonthlyDistributionService
{
    public function getAll()
    {
        $companyId = Company::query()->firstOrFail()->id;

        return MonthlyDistribution::query()
            ->with('lines')
            ->where('company_id', $companyId)
            ->latest()
            ->get();
    }

    public function create(array $data): MonthlyDistribution
    {
        $this->validateTotalPercentage($data['lines']);

        return DB::transaction(function () use ($data) {
            $company = Company::query()->firstOrFail();

            $distribution = MonthlyDistribution::query()->create([
                'company_id' => $company->id,
                'title' => $data['title'],
                'fiscal_year' => $data['fiscal_year'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth('api')->id(),
            ]);

            foreach ($data['lines'] as $line) {
                $distribution->lines()->create([
                    'month' => $line['month'],
                    'percentage' => $line['percentage'],
                ]);
            }

            return $distribution->fresh()->load('lines');
        });
    }

    public function update(MonthlyDistribution $distribution, array $data): MonthlyDistribution
    {
        $this->validateTotalPercentage($data['lines']);

        return DB::transaction(function () use ($distribution, $data) {
            $distribution->update([
                'title' => $data['title'],
                'fiscal_year' => $data['fiscal_year'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $distribution->lines()->delete();

            foreach ($data['lines'] as $line) {
                $distribution->lines()->create([
                    'month' => $line['month'],
                    'percentage' => $line['percentage'],
                ]);
            }

            return $distribution->fresh()->load('lines');
        });
    }

    public function delete(MonthlyDistribution $distribution): void
    {
        $distribution->delete();
    }

    private function validateTotalPercentage(array $lines): void
    {
        $months = collect($lines)->pluck('month');

        if ($months->duplicates()->isNotEmpty()) {
            throw new RuntimeException('Duplicate months are not allowed.');
        }

        $total = collect($lines)->sum(fn ($line) => (float) $line['percentage']);

        if (round($total, 2) !== 100.00) {
            throw new RuntimeException('Monthly distribution total percentage must equal 100%.');
        }
    }
}