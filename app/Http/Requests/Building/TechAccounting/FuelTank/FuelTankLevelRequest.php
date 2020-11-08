<?php

namespace App\Http\Requests\Building\TechAccounting\FuelTank;

use Illuminate\Foundation\Http\FormRequest;

class FuelTankLevelRequest extends FormRequest
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
            'fuel_level' => 'required|numeric|min:0|max:10000000000',
            'description' => 'required|string',
        ];
    }
}
