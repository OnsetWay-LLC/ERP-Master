<?php

namespace App\Http\Requests\SalesReturn;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.sales_returns') === true;
    }

    public function rules(): array
    {
        return [
            'sales_invoice_id' => ['required', 'exists:sales_invoices,id'],
            'posting_date' => ['required', 'date'],
            'posting_time' => ['required', 'date_format:H:i'],
            'payment_due_date' => ['nullable', 'date'],
            'return_reason' => ['nullable', 'string'],

            'posting_method' => ['nullable', 'in:default,manual'],

            'sales_account_id' => [
                'required_if:posting_method,manual',
                'nullable',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('account_type', 'direct_income')
                    ->where('account_level', 'child')
                    ->whereNull('deleted_at'),
            ],

            'customer_account_id' => [
                'required_if:posting_method,manual',
                'nullable',
                Rule::exists('chart_of_accounts', 'id')
                    ->where('account_type', 'receivable')
                    ->where('account_level', 'child')
                    ->whereNull('deleted_at'),
            ],

            'discount_apply_on' => ['nullable', 'in:grand_total,net_total'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_invoice_item_id' => ['required', 'exists:sales_invoice_items,id'],
            'items.*.returned_qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.warehouse_id' => ['required', 'exists:warehouses,id'],

            'tax_template_ids' => ['nullable', 'array'],
            'tax_template_ids.*' => ['exists:tax_templates,id'],

            'fees_template_ids' => ['nullable', 'array'],
            'fees_template_ids.*' => ['exists:fees_templates,id'],
        ];
    }
}