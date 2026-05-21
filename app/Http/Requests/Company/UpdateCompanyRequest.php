<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
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
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['sometimes', 'string', 'max:255'],

            'country' => [
                'sometimes',
                'string',
                Rule::in(array_keys(config('company.countries'))),
            ],

            'established_at' => ['nullable', 'date'],
            'fax' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],

            'working_days' => ['sometimes', 'array', 'size:7'],

            'working_days.*.day' => [
                'required_with:working_days',
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

            'working_days.*.is_working_day' => [
                'required_with:working_days',
                'boolean',
            ],
        ];
    }
}