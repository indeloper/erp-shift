<?php

namespace App\Http\Requests\UserRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserUpdateRequest extends FormRequest
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

    public function messages()
    {
        return [
            'first_name.required' => 'Поле имя обязательно для заполнения',
            'first_name.max' => 'Максимальное число символов : 50',

            'last_name.required'  => 'Поле фамилия обязательно для заполнения',
            'last_name.max' => 'Максимальное число символов : 50',

            'patronymic.max' => 'Максимальное число символов : 50',

            'group_id.required'  => 'Поле должность обязательно для заполнения',

            'company.required'  => 'Поле КОМПАНИЯ обязательно для заполнения',

            'birthday.required'  => 'Поле День Рождения обязательно для заполнения',
            'birthday.date'  => 'Поле День Рождения должно быть датой',

            'work_phone.required'  => 'Поле рабочий телефон обязательно для заполнения',
            'person_phone.required'  => 'Поле телефон обязательно для заполнения',

            'work_phone.unique'  => 'Поле рабочий телефон должно быть уникальным',
            'person_phone.unique'  => 'Поле телефон должно быть уникальным',

            'work_phone.max'  => 'Максимальное количество символов: 5',
            'person_phone.max'  => 'Максимальное количество символов: 11',
            'work_phone.min'  => 'Минимальное количество символов: 5',
            'person_phone.min'  => 'Минимальное количество символов: 11',

            'email.required'  => 'Поле email обязательно для заполнения',
            'email.unique' => 'Пользователь с таким email уже существует',

            'password.min'  => 'Минимальная длина пароля 7 символов',
            'password_confirmation.same'  => 'Пароли должны совпадать',

            'status.required'  => 'Поле статус обязательно для заполнения'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

     protected function prepareForValidation()
     {
         if ($this->has('person_phone')) {
             $this->merge(['person_phone' => preg_replace('~[\D]~', '', $this->person_phone)]);
         }
     }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'patronymic' => 'nullable|string|max:50',
            'group_id' => Auth::user()->can('users_edit') ? 'required_if:can_edit,1|integer|exists:groups,id' : '',
            'company' => Auth::user()->can('users_edit') ? 'required_if:can_edit,0|integer' : '',
            'birthday' => 'nullable|max:10',
            'person_phone' => 'nullable|string|min:11|max:11|unique:users,person_phone,' . $this->id,
            'work_phone' => 'nullable|string|unique:users,work_phone,' . $this->id,
            'email' => $this->email ? ('required|max:100|email|unique:users,email,' . $this->id) : '',
            'password' => $this->password ? 'min:7|regex:/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])/' : '',
            'password_confirmation' => 'same:password',
            'status' => 'boolean',
            'user_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'chat_id' => 'nullable|string|max:50',
        ];
    }
}
