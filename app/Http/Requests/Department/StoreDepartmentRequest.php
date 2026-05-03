<?php

namespace App\Http\Requests\Department;
use App\Models\Company;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();

        return $user && $user->can('screen.departments');
    }

    public function rules(): array
{
    $companyId = Company::query()->value('id');

    return [
        'name_ar' => [
            'required',
            'string',
            'max:255',
            Rule::unique('departments', 'name_ar')
                ->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
        ],
        'name_en' => [
            'required',
            'string',
            'max:255',
            Rule::unique('departments', 'name_en')
                ->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
        ],
    ];
}
}