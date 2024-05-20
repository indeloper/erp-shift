<?php

namespace App\Http\Requests\VehicleCategoryRequests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleCategoryDestroyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return boolval(auth()->user()->hasPermission('tech_acc_vehicle_category_destroy'));
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
