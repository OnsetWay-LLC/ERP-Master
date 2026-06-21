<?php

namespace App\Http\Requests\SalesPerson;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesPersonCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.sales_persons');
    }

    public function rules(): array
    {
        return [
           
            'sales_person_target_id' => [
                'required',
                'exists:sales_person_targets,id',
            ],

            'fiscal_year' => [
                'required',
                'integer',
                'min:2020',
            ],
        ];
    }
}