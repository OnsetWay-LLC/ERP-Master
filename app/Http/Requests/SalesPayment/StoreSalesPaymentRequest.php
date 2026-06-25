<?php

namespace App\Http\Requests\SalesPayment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.sales_payments') === true;
    }

    public function rules(): array
    {
        return [
            'sales_invoice_id' => [
                'required',
                'exists:sales_invoices,id',
            ],

            'payment_date' => ['required', 'date'],
            'payment_time' => ['required', 'date_format:H:i'],

            'posting_method' => ['nullable', 'in:default,manual'],
            'payment_mode' => ['required', 'in:cash,bank'],

            'paid_amount' => ['required', 'numeric', 'min:0.01'],

           'receivable_account_id' => [
    'nullable',
    'required_if:posting_method,manual',
    Rule::exists('chart_of_accounts', 'id')
        ->where('account_type', 'receivable')
        ->where('account_level', 'child')
        ->whereNull('deleted_at'),
],

           'payment_account_id' => [
    'nullable',
    'required_if:posting_method,manual',
    Rule::exists('chart_of_accounts', 'id')
        ->where('account_level', 'child')
        ->whereNull('deleted_at'),
],
        ];
    }
}