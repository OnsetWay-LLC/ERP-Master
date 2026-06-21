<?php

namespace App\Http\Requests\PaymentEntries;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
         return auth('api')->user()?->can('screen.purchase_payments') === true;
    }

    public function rules(): array
    {
        return [
            'posting_date' => ['required', 'date'],
            'payment_mode' => ['required', 'in:cash,bank'],
            'paid_amount' => ['required', 'numeric', 'gt:0'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'reference_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}