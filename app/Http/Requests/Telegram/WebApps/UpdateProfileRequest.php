<?php

namespace App\Http\Requests\Telegram\WebApps;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'         => 'required|email',
            'INN'           => 'required|string|min:12|max:12',
            'first_name'    => 'required|string',
            'last_name'     => 'required|string',
            'patronymic'    => 'nullable|string',
            'birthday'      => 'nullable|date',
            'person_phone'  => 'nullable|string',
            'work_phone'    => 'nullable|string',
            'accept_policy' => 'required',
        ];
    }

}
