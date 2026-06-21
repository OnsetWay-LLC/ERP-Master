<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class SalesPaymentSummaryReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.sales_payment_summary_report') === true;
            
    }

    public function rules(): array
    {
        return [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}