<?php

namespace App\Http\Requests\ItemGroup;

use Illuminate\Foundation\Http\FormRequest;

class IndexItemGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.item_groups');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'trashed' => ['nullable', 'in:with,only'],
        ];
    }
}