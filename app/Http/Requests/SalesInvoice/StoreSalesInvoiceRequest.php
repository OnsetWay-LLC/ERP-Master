<?php

namespace App\Http\Requests\SalesInvoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.sales_invoices');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'posting_date' => ['required', 'date'],
            'posting_time' => ['required'],
            'payment_due_date' => ['nullable', 'date', 'after_or_equal:posting_date'],
            
            'posting_method' => ['required', 'in:default,manual'],
            'receivable_account_id' => ['required_if:posting_method,manual', 'nullable', 'exists:chart_of_accounts,id'],
            'sales_account_id' => ['required_if:posting_method,manual', 'nullable', 'exists:chart_of_accounts,id'],
            'cogs_account_id' => ['required_if:posting_method,manual', 'nullable', 'exists:chart_of_accounts,id'],
            'stock_account_id' => ['required_if:posting_method,manual', 'nullable', 'exists:chart_of_accounts,id'],
            
            'payment_mode' => ['required', 'in:cash,bank,credit'],
            'payment_account_id' => ['required_unless:payment_mode,credit', 'nullable', 'exists:chart_of_accounts,id'],
            
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.warehouse_id' => ['required', 'exists:warehouses,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            
            'tax_template_ids' => ['nullable', 'array'],
            'tax_template_ids.*' => ['exists:tax_templates,id'],
            
            'fees_template_ids' => ['nullable', 'array'],
            'fees_template_ids.*' => ['exists:fees_templates,id'],
        ];
    }
}