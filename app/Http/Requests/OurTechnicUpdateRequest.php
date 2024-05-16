<?php

namespace App\Http\Requests;

use App\Models\TechAcc\CategoryCharacteristic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class OurTechnicUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator)
    {
        if (! $this->characteristics) {
            return;
        }
        foreach ($this->characteristics as $key => $characteristic) {
            if ($this->isEmptyRequiredParameter($characteristic)) {
                $validator->errors()->add("characteristics.{$key}.required", 'Поле является обязательным');
                throw new ValidationException($validator);
            }
        }
    }

    public function isEmptyRequiredParameter($characteristic): bool
    {
        return boolval(CategoryCharacteristic::find($characteristic['id'])->required ?? false) and empty($characteristic['value']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'brand' => 'sometimes|required|string|max:60',
            'model' => 'sometimes|required|string|max:60',
            'owner' => 'sometimes|required',
            'start_location_id' => 'sometimes|required',
            'technic_category_id' => 'sometimes|required',
            'exploitation_start' => 'sometimes|required',
            'inventory_number' => 'sometimes|required|max:30',
            'characteristics' => 'nullable|array',
            'characteristics.*' => 'nullable|array',
            'characteristics.*.value' => 'nullable|string|max:60',
            'characteristics.*.id' => 'required_unless:characteristics->count(),0|int',
        ];
    }
}
