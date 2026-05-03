<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.warehouses');
    }

    public function rules(): array
    {
        return [
            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name_ar')
                    ->where(fn ($q) => $q->where('company_id', $this->warehouse->company_id))
                    ->ignore($this->warehouse->id),
            ],
            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('warehouses', 'name_en')
                    ->where(fn ($q) => $q->where('company_id', $this->warehouse->company_id))
                    ->ignore($this->warehouse->id),
            ],
        ];
    }
}