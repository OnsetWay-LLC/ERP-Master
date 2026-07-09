<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();

        return $user && $user->can('screen.company');
    }

    public function failedAuthorization(): void
    {
        abort(403, 'You do not have permission to access the company screen.');
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],

            'country' => [
                'required',
                'string',
                Rule::in(array_keys(config('company.countries'))),
            ],

            'established_at' => ['nullable', 'date'],
            'fax' => ['nullable', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],

            'working_days' => ['required', 'array', 'size:7'],

            'working_days.*.day' => [
                'required',
                Rule::in([
                    'saturday',
                    'sunday',
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                ]),
            ],
            'report_header' => ['nullable', 'string'],
'report_footer' => ['nullable', 'string'],
'show_logo_in_reports' => ['nullable', 'boolean'],
'show_company_info_in_reports' => ['nullable', 'boolean'],

            'working_days.*.is_working_day' => ['required', 'boolean'],
        ];
    }
}