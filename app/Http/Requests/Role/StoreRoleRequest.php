<?php
namespace App\Http\Requests\Role;
use Illuminate\Foundation\Http\FormRequest;
class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.roles');
    }

     public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ];
    }
}