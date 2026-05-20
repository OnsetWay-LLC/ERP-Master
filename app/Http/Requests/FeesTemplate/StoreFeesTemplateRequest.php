<?php
namespace App\Http\Requests\FeesTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreFeesTemplateRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check()&& auth()->user()->can('screen.fees_templates');
    }
    public function rules()
    {
        return [
           'title' => [
    'required',
    'string',
    'max:255',
    Rule::unique('fees_templates', 'title')
        ->where('company_id', 1)
        ->whereNull('deleted_at'),
],


'type' => [
    'required',
    Rule::in(['percentage', 'fixed_amount'])
],

'account_id' => [
    'required',
    'exists:chart_of_accounts,id'
],

'fees_rate' => [
    'required_if:type,percentage',
    'nullable',
    'numeric',
    'min:0'
],

'amount' => [
    'required_if:type,fixed_amount',
    'nullable',
    'numeric',
    'min:0'
],
        ];
    }
}