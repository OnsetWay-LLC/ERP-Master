<?php

namespace App\Http\Requests\DeliveryNote;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryNoteFromPickListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.delivery_notes') === true;
    }

    public function rules(): array
    {
        return [];
    }
}