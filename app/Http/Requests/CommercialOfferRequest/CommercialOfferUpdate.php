<?php

namespace App\Http\Requests\CommercialOfferRequest;

use Illuminate\Foundation\Http\FormRequest;

class CommercialOfferUpdate extends FormRequest
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
            'document' => 'required|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,dwg,dwl,dwl2,dxf,mpp,gif,bmp,txt,rtf,pptx',
        ];
    }
}
