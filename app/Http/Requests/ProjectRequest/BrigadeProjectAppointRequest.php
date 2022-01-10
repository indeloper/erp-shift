<?php

namespace App\Http\Requests\ProjectRequest;

use App\Models\HumanResources\Brigade;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BrigadeProjectAppointRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('human_resources_project_brigade_assignment')) and auth()->user()->isProjectTimeResponsibleOrProjectResponsibleRP($this->request->get('project_id'));
    }

    public function withValidator($validator) {
        $isAttached = Brigade::find($this->request->get('brigade_id'))->appointments()->where('project_id', $this->request->get('project_id'))->exists();
        if ($isAttached) {
            $validator->errors()->add("name", 'Бригада уже назначена на этот проект, её нельзя назначить ещё раз');
            throw new ValidationException($validator);
        }
    }

    public function rules()
    {
        return [
            'brigade_id' => ['required', 'exists:brigades,id'],
            'project_id' => ['required', 'exists:projects,id'],
        ];
    }
}
