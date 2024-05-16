<?php

namespace App\Http\Requests\Building\TechAccounting\FuelTank;

use Illuminate\Foundation\Http\FormRequest;

class FuelTankRequest extends FormRequest
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
            'tank_number' => 'required|string|max:10',
            'object_id' => 'required|exists:project_objects,id',
            'explotation_start' => 'required|date',
        ];
    }
}
