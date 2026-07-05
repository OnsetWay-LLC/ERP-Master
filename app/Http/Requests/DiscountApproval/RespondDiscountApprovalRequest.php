<?php

namespace App\Http\Requests\DiscountApproval;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondDiscountApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()->can('screen.notifications');
    }

   public function rules(): array
{
    return [
        'action' => [
            'required',
            Rule::in([
                'approve',
                'reject',
                'forward_to_cfo',
            ]),
        ],

        'rejection_reason' => [
            'required_if:action,reject',
            'nullable',
            'string',
            'max:1000',
        ],
    ];
}
}