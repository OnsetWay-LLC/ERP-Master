<?php

namespace App\Http\Requests\DiscountSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateDiscountSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
             $user = auth('api')->user();
          return $user && $user->can('screen.discount_settings');
    }

    public function rules(): array
    {
        return [
            'sub_accountant_max_discount' => [
                'sometimes', 'nullable',
                'string',
                'min:0',
                'max:100',
            ],

            'department_manager_max_discount' => [
                'sometimes', 'nullable',
                'string',
                'min:0',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sub_accountant_max_discount.sometimes' => 'Sub Accountant max discount is not allowed.',
            'sub_accountant_max_discount.numeric' => 'Sub Accountant max discount must be numeric.',
            'sub_accountant_max_discount.min' => 'Sub Accountant max discount cannot be less than 0.',
            'sub_accountant_max_discount.max' => 'Sub Accountant max discount cannot exceed 100.',

            'department_manager_max_discount.sometimes' => 'Department Manager max discount is not allowed.',
            'department_manager_max_discount.numeric' => 'Department Manager max discount must be numeric.',
            'department_manager_max_discount.min' => 'Department Manager max discount cannot be less than 0.',
            'department_manager_max_discount.max' => 'Department Manager max discount cannot exceed 100.',
        ];
    }
}