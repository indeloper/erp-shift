<?php

namespace App\Http\Requests\Building\MaterialAccounting;

use Illuminate\Foundation\Http\FormRequest;

class SendTransformationRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'materials_to' => 'nullable|array',
            'materials_to.*.material_id' => 'sometimes|required|exists:manual_materials,id',
            'materials_to.*.material_unit' => 'sometimes|required',
            'materials_to.*.material_count' => 'sometimes|required',
            'materials_to.*.material_date' => 'sometimes|required|before_or_equal:now',

            'materials_from' => 'nullable|array',
            'materials_from.*.material_id' => 'sometimes|required|exists:manual_materials,id',
            'materials_from.*.material_unit' => 'sometimes|required',
            'materials_from.*.material_count' => 'sometimes|required',
            'materials_from.*.material_date' => 'sometimes|required|before_or_equal:now',
        ];
    }
}
