<?php

namespace App\Http\Requests\VehicleCategoryRequests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleCategoryUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return boolval(auth()->user()->hasPermission('tech_acc_vehicle_category_edit'));
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
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'min:1', 'max:60'],
            'description' => ['nullable', 'string', 'min:1', 'max:200'],
            'deleted_characteristic_ids' => ['nullable', 'array'],
            'characteristics' => ['nullable', 'array', 'max:10'],
            'characteristics.*.name' => ['required', 'string', 'min:1', 'max:60'],
            'characteristics.*.unit' => ['nullable', 'string', 'max:10'],
            'characteristics.*.show' => ['required', 'boolean'],
            'characteristics.*.required' => ['required', 'boolean'],
            'characteristics.*.short_name' => ['nullable', 'string', 'max:30'],
        ];
    }
}
