<?php

namespace App\Http\Requests\ManualRequests;

use Illuminate\Foundation\Http\FormRequest;

class NodeRequest extends FormRequest
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

    public function prepareForValidation()
    {
        if ($this->has('materials')) {
            $materials = array_unique(explode(',', $this->materials));
            $this->merge(['materials' => $materials]);
        }

        if ($this->has('count')) {
            $count = explode(',', $this->count);
            $diff_keys = array_keys(array_diff_key($count, $materials));

            foreach ($diff_keys as $diff) {
                unset($count[$diff]);
            }
            $this->merge(['count' => $count]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'node_id' => 'sometimes|required|numeric|exists:manual_nodes,id',
            'materials' => 'array|required',
            'materials.*' => 'numeric|exists:manual_materials,id',
            'count' => 'array|required',
            'node_category_id' => 'string|required|exists:manual_node_categories,id',
            'node_name' => 'string|required|max:150',
            'node_description' => 'string|nullable|max:200',
        ];
    }
}
