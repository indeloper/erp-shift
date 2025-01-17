<?php

namespace App\Http\Requests\SupportRequests;

use Illuminate\Foundation\Http\FormRequest;

class SupportMailRequest extends FormRequest
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
            'page_path' => 'required',
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:1000',
            'images' => 'nullable|array|max:20',
            'images.*' => 'required|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,dwg,mpp,gif,bmp,txt,rtf,pptx',
        ];
    }
}
