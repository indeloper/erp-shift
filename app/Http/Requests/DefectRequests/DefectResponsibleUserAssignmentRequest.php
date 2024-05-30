<?php

namespace App\Http\Requests\DefectRequests;

use Illuminate\Foundation\Http\FormRequest;

class DefectResponsibleUserAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return boolval(auth()->user()->hasPermission('tech_acc_defects_responsible_user_assignment'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
        ];
    }
}
