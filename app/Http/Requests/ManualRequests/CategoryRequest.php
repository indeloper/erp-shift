<?php

namespace App\Http\Requests\ManualRequests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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

    protected $redirectRoute = 'request_error';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'sometimes|required|numeric|exists:manual_material_categories,id',
            'name' => 'required|max:50',
            'category_unit' => 'required|max:50',
            'formula' => 'nullable|max:1000',
            'description' => 'nullable|max:250',
            'attrs.*.name' => 'required|max:50',
            'attrs.*.unit' => 'nullable|max:30',
            'attrs.*.from' => 'nullable',
            'attrs.*.to' => 'nullable',
            'attrs.*.step' => 'nullable',
            'attrs.*.value' => 'nullable|max:30',
            'attrs.*.is_required' => 'required',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('attrs')) {
            $this->merge(['attrs' => json_decode($this->attrs, true)]);
        }
    }
}
