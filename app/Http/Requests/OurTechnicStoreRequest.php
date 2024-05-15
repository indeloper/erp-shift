<?php

namespace App\Http\Requests;

use App\Models\TechAcc\CategoryCharacteristic;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class OurTechnicStoreRequest extends FormRequest
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
        return boolval(CategoryCharacteristic::find($characteristic['id'])->required) and empty($characteristic['value']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'brand' => 'required|string|max:60',
            'model' => 'required|string|max:60',
            'owner' => 'required',
            'start_location_id' => 'required',
            'technic_category_id' => 'required',
            'exploitation_start' => 'required',
            'inventory_number' => 'required|max:30',
            'characteristics' => 'nullable|array',
            'characteristics.*' => 'nullable|array',
            'characteristics.*.value' => 'nullable|string|max:60',
            'characteristics.*.id' => 'required_unless:characteristics->count(),0|int',
        ];
    }
}
