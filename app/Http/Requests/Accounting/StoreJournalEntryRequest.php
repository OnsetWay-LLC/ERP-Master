<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();

        if (! $user) {
            return false;
        }

        if ($this->isMethod('post')) {
            return $user->can('screen.journal_entries.create');
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $user->can('screen.journal_entries.update');
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],

            'lines' => ['required', 'array', 'min:2'],

            'lines.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string'],
        ];
    }
}