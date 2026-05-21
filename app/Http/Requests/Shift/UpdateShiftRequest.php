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
            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('shifts', 'name_ar')->ignore($shiftId),
            ],
            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('shifts', 'name_en')->ignore($shiftId),
            ],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}