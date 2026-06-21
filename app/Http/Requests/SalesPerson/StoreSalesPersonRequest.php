<?php

namespace App\Http\Requests\SalesPerson;

use App\Models\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreSalesPersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();

        return $user?->can('screen.sales_persons') === true;
    }

    public function rules(): array
    {
        return [
            Rule::unique('sales_people', 'employee_id')
    ->whereNull('deleted_at'),
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
             'user_id' => [
    'nullable',
    'integer',
    'exists:users,id',
],
             Rule::unique('sales_people', 'user_id')
    ->whereNull('deleted_at'),
            'sales_person_name_ar' => ['required', 'string', 'max:255'],
            'sales_person_name_en' => ['required', 'string', 'max:255'],

            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],

            'use_default_account' => ['required', 'boolean'],

            'commission_account_id' => [
                'nullable',
                'required_if:use_default_account,false',
                'exists:chart_of_accounts,id',
            ],

            'payroll_account_id' => [
                'nullable',
                'required_if:use_default_account,false',
                'exists:chart_of_accounts,id',
            ],

            'targets' => ['required', 'array', 'min:1'],
            'targets.*.item_group_id' => ['required', 'exists:item_groups,id'],
            'targets.*.target_amount' => ['required', 'numeric', 'min:0.01'],
            'targets.*.monthly_distribution_id' => ['required', 'exists:monthly_distributions,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->boolean('use_default_account')) {
                return;
            }

            $commissionAccount = ChartOfAccount::query()
                ->where('id', $this->commission_account_id)
                ->whereNull('deleted_at')
                ->first();

            if ($commissionAccount && $commissionAccount->account_type !== 'direct_expense') {
                $validator->errors()->add(
                    'commission_account_id',
                    'Commission account must be Direct Expense account.'
                );
            }

            if ($commissionAccount && $commissionAccount->account_level !== 'child') {
                $validator->errors()->add(
                    'commission_account_id',
                    'Commission account must be child account.'
                );
            }

            if ($commissionAccount && !$commissionAccount->is_active) {
                $validator->errors()->add(
                    'commission_account_id',
                    'Commission account must be active.'
                );
            }

            $payrollAccount = ChartOfAccount::query()
                ->where('id', $this->payroll_account_id)
                ->whereNull('deleted_at')
                ->first();

            if ($payrollAccount && $payrollAccount->account_type !== 'payable') {
                $validator->errors()->add(
                    'payroll_account_id',
                    'Payroll account must be Payable account.'
                );
            }

            if ($payrollAccount && $payrollAccount->account_level !== 'child') {
                $validator->errors()->add(
                    'payroll_account_id',
                    'Payroll account must be child account.'
                );
            }

            if ($payrollAccount && !$payrollAccount->is_active) {
                $validator->errors()->add(
                    'payroll_account_id',
                    'Payroll account must be active.'
                );
            }
        });
    }
}