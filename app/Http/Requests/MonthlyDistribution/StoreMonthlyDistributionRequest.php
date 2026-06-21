<?php

namespace App\Http\Requests\MonthlyDistribution;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyDistributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.monthly_distributions') === true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'fiscal_year' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],

            'lines' => ['required', 'array', 'size:12'],
            'lines.*.month' => ['nullable', 'integer', 'between:1,12'],
            'lines.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}