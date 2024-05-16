<?php

namespace App\Http\Requests\OurVehicleRequests;

use App\Models\TechAcc\Vehicles\VehicleCategoryCharacteristics;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class OurVehicleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('tech_acc_our_vehicle_create'));
    }

    public function withValidator($validator)
    {
        if (! $this->parameters) {
            return;
        }
        foreach ($this->parameters as $key => $parameter) {
            if ($this->isEmptyRequiredParameter($parameter)) {
                $validator->errors()->add("parameters.{$key}.required", 'Поле является обязательным');
                throw new ValidationException($validator);
            }
        }
    }

    public function isEmptyRequiredParameter($parameter): bool
    {
        return boolval(VehicleCategoryCharacteristics::find($parameter['characteristic_id'])->required) and empty($parameter['value']);
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_id' => ['required', 'exists:vehicle_categories,id'],
            'number' => ['required', 'string', 'max:9'],
            'trailer_number' => ['nullable', 'string', 'max:9'],
            'mark' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'owner' => ['required', 'numeric', 'min:1'],
            'parameters' => ['nullable', 'array', 'max:10'],
            'parameters.*.characteristic_id' => ['nullable', 'exists:vehicle_category_characteristics,id'],
            'parameters.*.value' => ['nullable', 'string', 'max:60'],
        ];
    }
}
