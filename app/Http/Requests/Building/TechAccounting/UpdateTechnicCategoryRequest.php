<?php

namespace App\Http\Requests\Building\TechAccounting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTechnicCategoryRequest extends FormRequest
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
            'name' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:200',
            'characteristics' => 'array:max:10',
            'characteristics.*.name' => 'nullable|string:max:60',
            'characteristics.*.id' => 'sometimes|integer',
            'characteristics.*.unit' => 'nullable|string:max:10',
            'characteristics.*.is_hidden' => 'nullable|boolean',
            'characteristics.*.description' => 'nullable|string:max:60',
            'characteristics.*.required' => 'required|boolean',
        ];
    }
}
