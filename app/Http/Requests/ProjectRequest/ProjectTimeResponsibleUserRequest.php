<?php

namespace App\Http\Requests\ProjectRequest;

use Illuminate\Foundation\Http\FormRequest;

class ProjectTimeResponsibleUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_project_time_responsible_user_change'));
    }

    public function rules()
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'time_responsible_user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
