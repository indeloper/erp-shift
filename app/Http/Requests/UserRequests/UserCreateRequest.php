<?php

namespace App\Http\Requests\UserRequests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    // protected $redirectRoute = 'request_error';

    public function messages(): array
    {
        return [
            'first_name.required' => 'Поле имя обязательно для заполнения',
            'first_name.max' => 'Максимальное число символов : 50',

            'last_name.required' => 'Поле фамилия обязательно для заполнения',
            'last_name.max' => 'Максимальное число символов : 50',

            'patronymic.max' => 'Максимальное число символов : 50',

            'group_id.required' => 'Поле ДОЛЖНОСТЬ обязательно для заполнения',

            'company.required' => 'Поле КОМПАНИЯ обязательно для заполнения',

            'birthday.required' => 'Поле День Рождения обязательно для заполнения',
            'birthday.date' => 'Поле День Рождения должно быть датой',

            'work_phone.required' => 'Поле рабочий телефон обязательно для заполнения',
            'person_phone.required' => 'Поле телефон обязательно для заполнения',

            'person_phone.max' => 'Максимальное количество символов: 11',
            'person_phone.min' => 'Минимальное количество символов: 11',

            'work_phone.unique' => 'Поле рабочий телефон должно быть уникальным',
            'person_phone.unique' => 'Поле телефон должно быть уникальным',

            'email.required' => 'Поле email обязательно для заполнения',
            'email.unique' => 'Пользователь с такой почтой уже существует',
            'email.max' => 'Максимальное число символов : 50',

            'password.required' => 'Поле пароль обязательно для заполнения',
            'password.min' => 'Минимальная длина пароля 7 символов',
            'password.regex' => 'Ваш пароль должен содержать символы верхнего и нижнего регистров, а так же цифры',
            'password_confirmation.required' => 'Поле повторите пароль обязательно для заполнения',
            'password_confirmation.same' => 'Пароли должны совпадать',

            'status.required' => 'Поле фамилия обязательно для заполнения',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('person_phone')) {
            $this->merge(['person_phone' => preg_replace('~[\D]~', '', $this->person_phone)]);
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'patronymic' => 'nullable|string|max:50',
            'group_id' => 'required|integer|exists:groups,id',
            'company' => 'required|integer',
            'birthday' => 'nullable|max:10',
            'person_phone' => 'nullable|string|min:11|max:17|unique:users,person_phone',
            'work_phone' => 'nullable|string|max:5|unique:users,work_phone',
            'email' => 'required_with:password|'.($this->email != null ? 'max:50|email|unique:users,email' : ''),
            'password' => 'required_with:email|'.($this->password != null ? 'min:7|regex:/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])/' : ''),
            'password_confirmation' => 'required_with:password|same:password',
            'status' => 'required|boolean',
            'user_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ];
    }
}
