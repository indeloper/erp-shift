<?php

namespace App\Http\Requests\ObjectRequests;

use Illuminate\Foundation\Http\FormRequest;

class ObjectRequest extends FormRequest
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

     public function messages(): array
     {
         return [
             'name.required' => 'Поле обязательно для заполнения',
             'name.max' => 'Максимальное число символов : 150',

             'address.required' => 'Поле обязательно для заполнения',
             'address.max' => 'Максимальное число символов : 250',

             'cadastral_number.max' => 'Максимальное число символов : 19',
             'cadastral_number.regex' => 'Кадастровый номер не соотвутствует стандарту',

             'material_accounting_type.required' => 'Поле обязательно для заполнения',
         ];
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'address' => 'required',
            'cadastral_number' => 'nullable|max:19|regex:/[0-9]{2}:[0-9]{2}:[0-9]{6,7}:[0-9]{1,5}/',
            'material_accounting_type' => 'required'
        ];
    }
}
