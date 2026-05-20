<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ProfitLossRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.profit_and_loss');
    }

    public function rules(): array
    {
        return [
            'financial_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }
}