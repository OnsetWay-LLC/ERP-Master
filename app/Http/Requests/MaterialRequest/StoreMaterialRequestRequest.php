<?php
namespace App\Http\Requests\MaterialRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('screen.material_requests');
    }
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'required_by_date' => ['nullable', 'date'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.required_qty' => ['required', 'numeric', 'min:1'],
        ];
    }
}