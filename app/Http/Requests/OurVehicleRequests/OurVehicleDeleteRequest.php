<?php

namespace App\Http\Requests\OurVehicleRequests;

use Illuminate\Foundation\Http\FormRequest;

class OurVehicleDeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return boolval(auth()->user()->hasPermission('tech_acc_our_vehicle_destroy'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
