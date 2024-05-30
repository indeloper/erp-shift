<?php

namespace App\Http\Requests\ProjectDocumentRequest;

use Illuminate\Foundation\Http\FormRequest;

class ProjectDocumentCreate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected $redirectRoute = 'request_error';

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'documents' => 'array',
            'documents.*' => 'required|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,dwg,dwl,dwl2,dxf,mpp,gif,bmp,txt,rtf,pptx',
        ];
    }
}
