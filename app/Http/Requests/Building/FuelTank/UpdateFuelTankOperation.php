<?php

namespace App\Http\Requests\Building\FuelTank;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFuelTankOperation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'our_technic_id' => 'required_if:type,2',
            'contractor_id' => 'required_if:type,1',
            'value' => 'required',
            'type' => 'required|in:1,2',
            'operation_date' => 'required|date',
        ];
    }
}
