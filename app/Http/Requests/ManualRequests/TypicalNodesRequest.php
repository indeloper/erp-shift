<?php

namespace App\Http\Requests\ManualRequests;

use Illuminate\Foundation\Http\FormRequest;

class TypicalNodesRequest extends FormRequest
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
            'id' => 'sometimes|required|numeric|exists:manual_node_categories,id',
            'category_name' => 'required|max:100',
            'category_description' => 'max:200',
            'safety_factor' => 'required|numeric|min:0|max:100',
        ];
    }
}
