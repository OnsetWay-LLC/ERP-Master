<?php

namespace App\Services\FinancialYear;

use App\Models\FinancialYear;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinancialYearService
{
    public function getAll(int $companyId): Collection
    {
        $this->autoPermanentlyCloseExpiredYears($companyId);

        return FinancialYear::query()
            ->where('company_id', $companyId)
            ->latest('start_date')
            ->get();
    }

    public function create(array $data, int $companyId, ?int $createdBy = null): FinancialYear
    {
        return DB::transaction(function () use ($data, $companyId, $createdBy) {
            $startDate = Carbon::parse($data['start_date']);
            $endDate = $startDate->copy()->endOfYear()->toDateString();

            return FinancialYear::query()->create([
                'company_id' => $companyId,
                'year_name' => $data['year_name'],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate,
                'closing_date' => $endDate,
                'grace_period_end' => Carbon::parse($endDate)->addMonths(2)->toDateString(),
                'status' => 'open',
                'created_by' => $createdBy,
            ]);
        });
    }

    public function update(FinancialYear $financialYear, array $data): FinancialYear
    {
        return DB::transaction(function () use ($financialYear, $data) {
            if ($financialYear->status !== 'open') {
                throw new InvalidArgumentException('Only open financial years can be updated.');
            }

            $payload = [];

            if (isset($data['year_name'])) {
                $payload['year_name'] = $data['year_name'];
            }

            if (isset($data['start_date'])) {
                $startDate = Carbon::parse($data['start_date']);
                $endDate = $startDate->copy()->endOfYear()->toDateString();

                $payload['start_date'] = $startDate->toDateString();
                $payload['end_date'] = $endDate;
                $payload['closing_date'] = $endDate;
                $payload['grace_period_end'] = Carbon::parse($endDate)->addMonths(2)->toDateString();
            }

            $financialYear->update($payload);

            return $financialYear->fresh();
        });
    }

    public function close(FinancialYear $financialYear, int $userId): FinancialYear
{
    return DB::transaction(function () use ($financialYear, $userId) {
        if ($financialYear->status !== 'open') {
            throw new InvalidArgumentException('Only open financial years can be closed.');
        }

        $financialYear->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $userId,
            'grace_period_end' => \Carbon\Carbon::parse($financialYear->end_date)
                ->addMonths(2)
                ->toDateString(),
        ]);

        return $financialYear->fresh();
    });
}

    public function reopen(FinancialYear $financialYear): FinancialYear
    {
        return DB::transaction(function () use ($financialYear) {
            if ($financialYear->status === 'permanently_closed') {
                throw new InvalidArgumentException('Permanently closed financial years cannot be reopened.');
            }

            $financialYear->update([
                'status' => 'open',
                'closed_at' => null,
                'closed_by' => null,
            ]);

            return $financialYear->fresh();
        });
    }

    public function delete(FinancialYear $financialYear): void
    {
        if ($financialYear->status !== 'open') {
            throw new InvalidArgumentException('Only open financial years can be deleted.');
        }

        $financialYear->delete();
    }

   public function validateTransactionDate(
    int $companyId,
    string $postingDate,
    string $actionType = 'create'
): void {
    $this->autoPermanentlyCloseExpiredYears($companyId);

    $year = FinancialYear::query()
        ->where('company_id', $companyId)
        ->whereDate('start_date', '<=', $postingDate)
        ->whereDate('end_date', '>=', $postingDate)
        ->first();

    if (! $year) {
        return;
    }

    if ($year->status === 'open') {
        return;
    }

    if ($year->status === 'closed') {
        if ($actionType === 'create') {
            throw new InvalidArgumentException(
                'Cannot create new transactions in a closed financial year. Please use the new financial year.'
            );
        }

        if ($actionType === 'update') {
            if (now()->toDateString() > $year->grace_period_end?->format('Y-m-d')) {
                $year->update([
                    'status' => 'permanently_closed',
                ]);

                throw new InvalidArgumentException(
                    'Grace period has ended. This financial year is permanently closed.'
                );
            }

            $user = auth('api')->user();

            if (! $user || ! $user->hasAnyRole(['CFO', 'Accountant Chief', 'Chief Accountant'])) {
                throw new InvalidArgumentException(
                    'Only CFO or Chief Accountant can update transactions during the grace period.'
                );
            }

            return;
        }
    }

    if ($year->status === 'permanently_closed') {
        throw new InvalidArgumentException(
            'Financial year is permanently closed.'
        );
    }
}
    public function autoPermanentlyCloseExpiredYears(int $companyId): void
    {
        FinancialYear::query()
            ->where('company_id', $companyId)
            ->where('status', 'closed')
            ->whereDate('grace_period_end', '<', now()->toDateString())
            ->update([
                'status' => 'permanently_closed',
            ]);
    }
}