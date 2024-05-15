<?php

namespace App\Http\Requests\DefectRequests;

use Illuminate\Foundation\Http\FormRequest;

class DefectStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return boolval(auth()->user()->hasPermission('tech_acc_defects_create') or auth()->user()->isProjectManager());
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'defectable_id' => ['required', 'numeric'],
            'defectable_type' => ['required', 'numeric', 'min:1', 'max:2'],
            'description' => ['required', 'string', 'min:1', 'max:300'],
        ];
    }
}
