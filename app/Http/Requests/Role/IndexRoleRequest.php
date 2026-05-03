<?php
namespace App\Http\Requests\Role;
use Illuminate\Foundation\Http\FormRequest;
class IndexRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.roles');
    }

       public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}