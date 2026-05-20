<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.shifts');
    }

    public function rules(): array
    {
        $shiftId = $this->route('shift')?->id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('shifts', 'name')->ignore($shiftId),
            ],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}