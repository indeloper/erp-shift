<?php

namespace App\Http\Requests\CommercialOffer;

use Illuminate\Foundation\Http\FormRequest;

class AddSubcontractorRequest extends FormRequest
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
            'subcontractor_works' => 'required|array',
            'subcontractor_id' => 'required',
        ];
    }
}
