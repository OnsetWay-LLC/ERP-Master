<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();

        return $user && $user->can('screen.departments');
    }

   public function rules(): array
{
    return [
       
        'name_ar' => [
            'required',
            'string',
            'max:255',
            'unique:departments,name_ar,' . $this->route('department')->id . ',id,company_id,' . $this->company_id . ',deleted_at,NULL'
        ],

        'name_en' => [
            'required',
            'string',
            'max:255',
            'unique:departments,name_en,' . $this->route('department')->id . ',id,company_id,' . $this->company_id . ',deleted_at,NULL'
        ],
    ];
}
}