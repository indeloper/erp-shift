<?php

namespace App\Http\Requests\Building\TechAccounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreTechnicCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->can('tech_acc_tech_category_create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:60',
            'description' => 'required|string|max:200',
            'characteristics' => 'array:max:10',
            'characteristics.*.name' => 'required|string:max:60',
            'characteristics.*.unit' => 'nullable|string:max:10',
            'characteristics.*.is_hidden' => 'required|boolean',
            'characteristics.*.description' => 'nullable|string:max:60',
            'characteristics.*.required' => 'required|boolean',
        ];
    }
}
