<?php

namespace App\Http\Requests\ProjectRequest;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserProjectAppointRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_user_to_project_appointment')) and auth()->user()->isProjectTimeResponsibleOrProjectResponsibleRP($this->request->get('project_id'));
    }

    public function withValidator($validator) {
        $isAttached = User::find($this->request->get('user_id'))->appointments()->where('project_id', $this->request->get('project_id'))->exists();
        if ($isAttached) {
            $validator->errors()->add("name", 'Пользователь уже назначен на этот проект, его нельзя назначить ещё раз');
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
