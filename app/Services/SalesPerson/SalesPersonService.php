<?php

namespace App\Services\SalesPerson;

use App\Models\Company;
use App\Models\Employee;
use App\Models\SalesPerson;
use App\Models\MonthlyDistribution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesPersonService
{
    private array $relations = [
        'employee',
        'user',
        'commissionAccount',
        'payrollAccount',
        'targets.itemGroup',
        'targets.monthlyDistribution',
        
    ];

    public function getAll()
    {
        $companyId = Company::query()->firstOrFail()->id;

        return SalesPerson::query()
            ->with($this->relations)
            ->where('company_id', $companyId)
            ->latest()
            ->get();
    }

    public function show(SalesPerson $salesPerson): SalesPerson
    {
        return $salesPerson->load($this->relations);
    }

    public function create(array $data): SalesPerson
    {
        return DB::transaction(function () use ($data) {
            $company = Company::query()->firstOrFail();

            $employee = Employee::query()
                ->where('company_id', $company->id)
                ->where('id', $data['employee_id'])
                ->firstOrFail();

            $user = User::query()
                ->where('employee_id', $employee->id)
                ->first();

            if ($user && !$user->hasRole('Sales Officer')) {
                throw new RuntimeException('Selected employee user must have Sales Officer role.');
            }

            $useDefaultAccount = (bool) $data['use_default_account'];

            $salesPerson = SalesPerson::query()->create([
                'company_id' => $company->id,
                'employee_id' => $employee->id,
                'user_id' => $user?->id,

                'sales_person_name_ar' => $data['sales_person_name_ar'],
                'sales_person_name_en' => $data['sales_person_name_en'],

                'commission_rate' => $data['commission_rate'],

                'use_default_account' => $useDefaultAccount,
                'commission_account_id' => $useDefaultAccount ? null : $data['commission_account_id'],
                'payroll_account_id' => $useDefaultAccount ? null : $data['payroll_account_id'],
                

                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth('api')->id(),
            ]);

            $this->saveTargets($salesPerson, $data['targets']);

            return $salesPerson->fresh()->load($this->relations);
        });
    }

    public function update(SalesPerson $salesPerson, array $data): SalesPerson
    {
        return DB::transaction(function () use ($salesPerson, $data) {
            $companyId = $salesPerson->company_id;

            $employee = Employee::query()
                ->where('company_id', $companyId)
                ->where('id', $data['employee_id'])
                ->firstOrFail();

            $user = User::query()
                ->where('employee_id', $employee->id)
                ->first();

            if ($user && !$user->hasRole('Sales Officer')) {
                throw new RuntimeException('Selected employee user must have Sales Officer role.');
            }

            $useDefaultAccount = (bool) $data['use_default_account'];

            $salesPerson->update([
                'employee_id' => $employee->id,
                'user_id' => $user?->id,

                'sales_person_name_ar' => $data['sales_person_name_ar'],
                'sales_person_name_en' => $data['sales_person_name_en'],

                'commission_rate' => $data['commission_rate'],

                'use_default_account' => $useDefaultAccount,
                'commission_account_id' => $useDefaultAccount ? null : $data['commission_account_id'],
                'payroll_account_id' => $useDefaultAccount ? null : $data['payroll_account_id'],

                'is_active' => $data['is_active'] ?? true,
            ]);

            $salesPerson->targets()->delete();

            $this->saveTargets($salesPerson, $data['targets']);

            return $salesPerson->fresh()->load($this->relations);
        });
    }

    public function delete(SalesPerson $salesPerson): void
    {
        $salesPerson->delete();
    }

    public function restore(int $id): SalesPerson
    {
        $salesPerson = SalesPerson::withTrashed()->findOrFail($id);

        if (!$salesPerson->trashed()) {
            throw new RuntimeException('Sales person is not deleted.');
        }

        $salesPerson->restore();

        return $salesPerson->fresh()->load($this->relations);
    }

    private function saveTargets(SalesPerson $salesPerson, array $targets): void
{
    foreach ($targets as $target) {

        MonthlyDistribution::query()
            ->where('id', $target['monthly_distribution_id'])
            ->where('company_id', $salesPerson->company_id)
            ->where('is_active', true)
            ->firstOrFail();

        $salesPerson->targets()->create([
            'item_group_id' => $target['item_group_id'],
            'monthly_distribution_id' => $target['monthly_distribution_id'],
            'target_amount' => $target['target_amount'],
        ]);
    }
}
    }
