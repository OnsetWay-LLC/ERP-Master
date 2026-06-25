<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class OwnerEquityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check()
            && auth('api')->user()->can('screen.owner_equity_report');
    }

    public function rules(): array
    {
        return [
            'financial_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }
}