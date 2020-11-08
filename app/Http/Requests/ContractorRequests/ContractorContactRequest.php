<?php

namespace App\Http\Requests\ContractorRequests;

use Illuminate\Foundation\Http\FormRequest;

class ContractorContactRequest extends FormRequest
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

     protected function prepareForValidation()
     {
         if ($this->has('phone_number')) {
             $this->merge(['phone_number' => preg_replace('~[\D]~', '', $this->phone_number)]);
         }
     }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'patronymic' => $this->patronymic ? ('string|max:50') : '',
            'position' => $this->position ? ('string|max:50') : '',
            'email' => $this->email ? ('email|max:50') : '',
            'phone_number.*' => 'string|max:11|distinct',
            'note' => $this->note ? ('string|max:200') : '',
        ];
    }
}
