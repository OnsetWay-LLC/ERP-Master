<?php
namespace App\Http\Requests\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.roles');
    }

       public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($this->role->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ];
    }
}