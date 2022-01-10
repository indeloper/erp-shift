<?php

namespace App\Http\Requests\ProjectRequest;

use App\Models\HumanResources\Appointment;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserProjectDetachRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_user_detach_from_project')) and auth()->user()->isProjectTimeResponsibleOrProjectResponsibleRP($this->request->get('project_id'));
    }

    public function withValidator($validator) {
        $isAttached = User::find($this->request->get('user_id'))->appointments()->where('project_id', $this->request->get('project_id'))->exists();
        if (! $isAttached) {
            $validator->errors()->add("user", 'Пользователь не был назначен на проект, его нельзя исключить с него');
            throw new ValidationException($validator);
        }
    }

    public function rules()
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'project_id' => ['required', 'exists:projects,id'],
        ];
    }
}
