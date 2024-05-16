<?php

namespace App\Http\Requests\ManualRequests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected $redirectRoute = 'request_error';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'sometimes|required|numeric|'.(($this->className == 'ManualReference') ? 'exists:manual_references,id' : 'exists:manual_materials,id'),
            'name' => 'required|max:255',
            'description' => 'nullable|max:250',
            'use_cost' => ($this->className == 'ManualReference') ? 'nullable' : 'required|min:1|max:20',
            'buy_cost' => ($this->className == 'ManualReference') ? 'nullable' : 'required|min:1|max:20',
            'attrs.*' => 'max:100',
            'document' => 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,dwg,dwl,dwl2,dxf,mpp,gif,bmp,txt,rtf,pptx',
            'className' => 'required|in:ManualReference,ManualMaterial',
        ];
    }
}
