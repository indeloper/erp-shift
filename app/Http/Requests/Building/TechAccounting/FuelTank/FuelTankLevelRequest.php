<?php

namespace App\Http\Requests\Building\TechAccounting\FuelTank;

use Illuminate\Foundation\Http\FormRequest;

class FuelTankLevelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'fuel_level' => 'required|numeric|min:0|max:10000000000',
            'description' => 'required|string',
        ];
    }
}
