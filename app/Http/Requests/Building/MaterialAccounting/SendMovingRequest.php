<?php

namespace App\Http\Requests\Building\MaterialAccounting;

use Illuminate\Foundation\Http\FormRequest;

class SendMovingRequest extends FormRequest
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

    public function messages()
    {
        return [
            'count_files.min' => 'Необходимо прикрепить к операции как минимум два изображения',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'count_files' => 'sometimes|numeric|min:2',
            'materials' => 'nullable|array',
            'materials.*.material_id' => 'sometimes|required|exists:manual_materials,id',
            'materials.*.material_unit' => 'sometimes|required',
            'materials.*.material_count' => 'sometimes|required',
            'materials.*.material_date' => 'sometimes|required|before_or_equal:now',
        ];
    }
}
