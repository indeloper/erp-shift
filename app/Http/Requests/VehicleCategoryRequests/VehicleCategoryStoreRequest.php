<?php

namespace App\Http\Requests\VehicleCategoryRequests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleCategoryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return boolval(auth()->user()->can('tech_acc_vehicle_category_create'));
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'min:1', 'max:60'],
            'description' => ['nullable', 'string', 'min:1', 'max:200'],
            'characteristics' => ['nullable', 'array', 'max:10'],
            'characteristics.*.name' => ['required', 'string', 'min:1', 'max:60'],
            'characteristics.*.unit' => ['nullable', 'string', 'max:10'],
            'characteristics.*.show' => ['required', 'boolean'],
            'characteristics.*.required' => ['required', 'boolean'],
            'characteristics.*.short_name' => ['nullable', 'string', 'max:30'],
        ];
    }
}
