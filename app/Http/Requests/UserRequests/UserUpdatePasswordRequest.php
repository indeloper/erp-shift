<?php

namespace App\Http\Requests\UserRequests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdatePasswordRequest extends FormRequest
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
            'password.required' => 'Поле пароль обязательно для заполнения',
            'password.min' => 'Минимальная длина пароля 7 символов',
            'password.regex' => 'Ваш пароль должен содержать символы верхнего и нижнего регистров, а так же цифры',
            'password_confirmation.required' => 'Поле повторите пароль обязательно для заполнения',
            'password_confirmation.same' => 'Пароли должны совпадать',
        ];
    }

    public function rules(): array
    {
        return [
            'password' => 'required|min:7|regex:/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])/',
            'password_confirmation' => 'required|same:password',
        ];
    }
}
