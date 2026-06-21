<?php

namespace App\Http\Requests\PayrollTaxSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePayrollTaxSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()?->can('screen.payroll_tax_settings') === true;
    }

    public function rules(): array
    {
        return [
            'single_or_working_wife_exemption' => ['required', 'numeric', 'min:0'],
            'married_not_working_wife_exemption' => ['required', 'numeric', 'min:0'],

            'brackets' => ['required', 'array', 'min:1'],

            // ما بدنا المستخدم يدخل from_amount
            'brackets.*.from_amount' => ['nullable', 'numeric', 'min:0'],

            // nullable مسموحة فقط لآخر شريحة
            'brackets.*.to_amount' => ['nullable', 'numeric', 'min:0'],

            'brackets.*.rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'brackets.*.sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $brackets = $this->input('brackets', []);

            $previousToAmount = 0;
            $lastIndex = count($brackets) - 1;

            foreach ($brackets as $index => $bracket) {
                $toAmount = $bracket['to_amount'] ?? null;

                if ($toAmount === null && $index !== $lastIndex) {
                    $validator->errors()->add(
                        "brackets.$index.to_amount",
                        'The to amount can be null only for the last bracket.'
                    );

                    continue;
                }

                if ($toAmount !== null && (float) $toAmount <= (float) $previousToAmount) {
                    $validator->errors()->add(
                        "brackets.$index.to_amount",
                        'The to amount must be greater than the previous bracket amount.'
                    );
                }

                if ($toAmount !== null) {
                    $previousToAmount = (float) $toAmount;
                }
            }
        });
    }
}