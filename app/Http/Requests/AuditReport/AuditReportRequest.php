<?php

namespace App\Http\Requests\AuditReport;

use Illuminate\Foundation\Http\FormRequest;

class AuditReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        return [
            'username' => ['nullable', 'string'],
            'operation' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
        ];
    }
}