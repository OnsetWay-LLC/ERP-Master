<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class AccountsReceivableReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.AccountsReceivableReport') === true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ];
    }
}