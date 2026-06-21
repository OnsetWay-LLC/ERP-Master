<?php

namespace App\Http\Requests\PurchaseInvoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.purchase_invoices') === true;
    }

    public function rules(): array
    {
        return [
            'purchase_receipt_id' => ['required', Rule::exists('purchase_receipts', 'id')->where('status', 'submitted')],
            
           'posting_date' => ['sometimes', 'nullable', 'date'],
           'posting_time' => ['sometimes', 'nullable', 'date_format:H:i:s'],
             'due_date' => ['sometimes', 'nullable', 'date'],
            'supplier_invoice_no' => ['nullable', 'string', 'max:255'],
            'supplier_invoice_date' => ['nullable', 'date'],

            'posting_method' => ['required', 'in:default,manual'],
            'payment_mode' => ['required', 'in:credit,cash,bank,cheque,credit_card'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            
            'stock_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'purchase_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'supplier_payable_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'cash_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'bank_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
        ];
    }
}