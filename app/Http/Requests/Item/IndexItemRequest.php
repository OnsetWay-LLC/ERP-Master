<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class IndexItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.items');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'item_group_id' => ['nullable', 'exists:item_groups,id'],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'trashed' => ['nullable', 'in:with,only'],
        ];
    }
}