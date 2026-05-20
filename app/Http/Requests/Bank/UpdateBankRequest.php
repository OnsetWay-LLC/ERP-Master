<?php

namespace App\Http\Requests\Bank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.bank');
    }

    public function rules(): array
    {
        $companyId = 1;
        $id = $this->route('id');

        return [
           'name_ar' => [
    'required',
    'string',
    'max:255',
    Rule::unique('banks', 'name_ar')
        ->where('company_id', 1)
        ->whereNull('deleted_at')
        ->ignore($id),
],

'name_en' => [
    'required',
    'string',
    'max:255',
    Rule::unique('banks', 'name_en')
        ->where('company_id', 1)
        ->whereNull('deleted_at')
        ->ignore($id),
],
        ];
    }
}