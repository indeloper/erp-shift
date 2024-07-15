<?php

namespace App\Http\Requests\ManualRequests;

use Illuminate\Foundation\Http\FormRequest;

class WorkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected $redirectRoute = 'request_error';

    /**
     * Get the validation rules that apply to the request.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Поле обязательно для заполнения',
            'name.max' => 'Максимальное число символов : 50',

            'description.max' => 'Максимальное число символов : 100',

            'price_per_unit.required' => 'Поле обязательно для заполнения',
            'price_per_unit.max' => 'Максимальное число символов : 20',

            'unit_per_days.required' => 'Поле обязательно для заполнения',
            'unit_per_days.max' => 'Максимальное число символов : 5',

            'unit.required' => 'Единица измерения обязательна для выбора',
            'unit.max' => 'Максимальное число символов : 15',

            'nds.required' => 'НДС обязателен для выбора',
            'nds.max' => 'Максимальное число символов : 5',

            'work_group.required' => 'Группа работ обязательна для выбора',
        ];
    }

    public function rules(): array
    {
        return [
            'work_id' => 'sometimes|required|numeric|exists:manual_works,id',
            'name' => 'required|max:150',
            'description' => 'nullable|max:200',
            'price_per_unit' => 'required|max:10',
            'unit_per_days' => 'required|max:5',
            'unit' => 'required|max:15',
            'nds' => 'required|max:5',
            'work_group' => 'required|numeric|between:1,5',
        ];
    }
}
