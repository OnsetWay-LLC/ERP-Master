<?php

namespace App\Http\Requests\DiscountApproval;

use Illuminate\Foundation\Http\FormRequest;

class RespondDiscountApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->hasRole('CFO') === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
        ];
    }
}