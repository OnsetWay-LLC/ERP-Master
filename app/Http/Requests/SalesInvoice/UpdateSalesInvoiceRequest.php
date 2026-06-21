<?php

namespace App\Http\Requests\SalesInvoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.sales_invoices') === true;
    }

    public function rules(): array
    {
        $companyId = 1;

        return [
            'sales_order_id' => [
                'nullable',
                Rule::exists('sales_orders', 'id')->where('company_id', $companyId),
            ],

            'delivery_note_id' => [
                'nullable',
                Rule::exists('delivery_notes', 'id')->where('company_id', $companyId),
            ],

            'customer_id' => ['required', 'exists:customers,id'],

            'sales_person_id' => [
                'required',
                Rule::exists('sales_people', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true),
            ],

            'posting_date' => ['required', 'date'],
            'posting_time' => ['required', 'date_format:H:i'],
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

            'items.*.sales_order_item_id' => ['nullable', 'exists:sales_order_items,id'],
            'items.*.delivery_note_item_id' => ['nullable', 'exists:delivery_note_items,id'],

            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.warehouse_id' => ['required', 'exists:warehouses,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],

            'tax_template_ids' => ['nullable', 'array'],
            'tax_template_ids.*' => ['exists:tax_templates,id'],

            'fees_template_ids' => ['nullable', 'array'],
            'fees_template_ids.*' => ['exists:fees_templates,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('delivery_note_id') && ! $this->input('sales_order_id')) {
                $validator->errors()->add(
                    'sales_order_id',
                    'Sales order is required when delivery note is selected.'
                );
            }

            if ($this->input('delivery_note_id')) {
                foreach ($this->input('items', []) as $index => $item) {
                    if (empty($item['delivery_note_item_id'])) {
                        $validator->errors()->add(
                            "items.$index.delivery_note_item_id",
                            'Delivery note item is required when invoice is linked to delivery note.'
                        );
                    }
                }
            }

            if ($this->input('payment_mode') === 'credit' && $this->input('payment_account_id')) {
                $validator->errors()->add(
                    'payment_account_id',
                    'Payment account is not allowed when payment mode is credit.'
                );
            }
        });
    }
}