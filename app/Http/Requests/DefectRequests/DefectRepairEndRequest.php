<?php

namespace App\Http\Requests\DefectRequests;

use Illuminate\Foundation\Http\FormRequest;

class DefectRepairEndRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'repair_end_date' => now(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:300'],
            'repair_end_date' => ['required', 'date'],
            'start_location_id' => ['required', 'exists:project_objects,id'],
        ];
    }
}
