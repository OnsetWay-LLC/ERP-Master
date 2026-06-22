<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ViewTaxDeclarationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check() &&
            auth('api')->user()->can('screen.tax_declaration_reports');
    }

    public function rules(): array
    {
        return [
            'year' => [
                'required',
                'integer',
                'digits:4',
                'min:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required' => 'The year field is required.',
            'year.integer'  => 'The year must be an integer.',
            'year.digits'   => 'The year must contain 4 digits.',
            'year.min'      => 'The year is invalid.',
        ];
    }
}