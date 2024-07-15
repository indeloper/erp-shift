<?php

namespace App\Http\Requests\Building\MaterialAccounting;

use Illuminate\Foundation\Http\FormRequest;

class AttachContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'operation_id' => 'required|exists:material_accounting_operations,id',
            'contract_id' => 'required|exists:contracts,id',
            'task_id' => 'required|exists:tasks,id',
        ];
    }
}
