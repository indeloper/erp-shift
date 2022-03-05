<?php

namespace App\Http\Requests\ProjectRequest;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected $redirectRoute = 'request_error';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */


     public function messages()
     {
         return [
             'contractor_ids.*.exist' => 'Контрагент не найден',
             'contractor_ids.*.required' => 'Поле КОНТРАГЕНТ обязательно для заполнения',

             'name.required' => 'Поле НАЗВАНИЕ ПРОЕКТА обязательно для заполнения',
             'name.max' => 'Максимальное число символов : 200',

             'object_address.max' => 'Максимальное число символов : 150',
             'object_address.required' => 'Поле АДРЕС ОБЪЕКТА обязательно для заполнения',
             'description.max' => 'Максимальное число символов : 200',
             'contractor_contact_ids.required' => 'Контакты не указаны. Необходимо добавить хотя бы одно контактное лицо в проект.',
         ];
    }

    public function rules()
    {
        return [
            'contractor_ids.*' => stristr(request()->pathInfo, 'update') ? 'nullable' : 'exists:contractors,id',
            'main_contractor' => 'exists:contractors,id',
            'name' => 'required|string|max:200',
            'entity' => 'required|in:1,2',
            'object_id' => 'required|exists:project_objects,id',
            'description' => $this->description ? 'string:max:200' : '',
            'contractor_contact_ids' => stristr(request()->pathInfo, 'update') ? 'nullable' : 'required'
        ];
    }
}
