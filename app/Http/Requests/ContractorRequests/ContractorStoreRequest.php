<?php

namespace App\Http\Requests\ContractorRequests;

use Illuminate\Foundation\Http\FormRequest;

class ContractorStoreRequest extends FormRequest
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
    public function messages()
    {
        return [
            'full_name.required' => 'Поле полное наименование обязательно для заполнения',
            'full_name.max' => 'Максимальное число символов : 200',
            'full_name.unique' => 'Контрагент с таким наименованием уже существует',

            'short_name.required' => 'Поле краткое наименование обязательно для заполнения',
            'short_name.max' => 'Максимальное число символов : 100',

            'inn.max' => 'Максимальное число цифр : 14',
            'inn.min' => 'Минимальное число цифр : 12',
            'inn.unique' => 'Контрагент с таким ИНН уже существует',

            'kpp.size' => 'Поле должно состоять из 9 цифр',
            'kpp.unique' => 'Контрагент с таким КПП уже существует',

            'ogrn.max' => 'Максимальное количество символов: 15',
            'ogrn.min' => 'Минимальное количество символов: 12',
            'ogrn.unique' => 'Контрагент с таким ОГРН уже существует',

            'legal_address.max' => 'Максимальное число символов : 200',
            'physical_adress.max' => 'Максимальное число символов : 200',

            'general_manager.max' => 'Максимальное число символов : 200',

            'phone_number.max' => 'Максимальное количество символов: 11',
            'phone_number.min' => 'Минимальное количество символов: 11',
            'phone_number.unique' => 'Поле телефон должно быть уникальным',

            'email.unique' => 'Контрагент с такой почтой уже существует',
            'email.max' => 'Максимальное число символов : 50',

            'bank_name.required' => 'Поле является обязательным',

            'check_account.required' => 'Поле является обязательным',
            'check_account.size' => 'Поле должно состоять из 20 цифр',

            'cor_account.required' => 'Поле является обязательным',
            'cor_account.size' => 'Поле должно состоять из 20 цифр',

            'bik.required' => 'Поле является обязательным',
            'bik.size' => 'Поле должно состоять из 9 цифр',

            'bik.string' => 'Поле должно состоять цифр',
            'cor_account.string' => 'Поле должно состоять цифр',
            'check_account.string' => 'Поле должно состоять цифр',
            'bank_name.string' => 'Поле должно быть строкой',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('phone_number')) {
            $this->merge(['phone_number' => preg_replace('~[\D]~', '', $this->phone_number)]);
        }
    }

    public function rules()
    {
        return [
            'full_name' => 'required|string|max:200|unique:contractors,full_name,'.($this ? $this->id : ''),
            'short_name' => 'required|string|max:200',
            'inn' => $this->inn ? ('string|min:10|max:14|unique:contractors,inn,'.($this ? $this->id : '')) : '',
            'kpp' => $this->kpp ? ('string|size:9') : '',
            'ogrn' => $this->ogrn ? ('string|min:12|max:15|unique:contractors,ogrn,'.($this ? $this->id : '')) : '',
            'legal_address' => $this->legal_address ? 'string|max:200' : '',
            'physical_adress' => $this->physical_adress ? 'string|max:200' : '',
            'general_manager' => $this->general_manager ? 'string|max:100' : '',
            'phone_number.*' => 'string|max:11|distinct',
            'phone_count' => 'array|required',
            'email' => $this->email ? ('max:50|email|unique:contractors,email,'.($this ? $this->id : '')) : '',

            'bank_name' => $this->bank_name ? ('string|max:100') : '',
            'check_account' => $this->check_account ? ('string|size:20') : '',
            'cor_account' => $this->cor_account ? ('string|size:20') : '',
            'bik' => $this->bik ? ('string|size:9') : '',
            'types' => 'required|array|min:1|max:3',
        ];
    }
}
