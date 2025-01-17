<?php

namespace App\Http\Requests\DefectRequests;

use Illuminate\Foundation\Http\FormRequest;

class DefectDeclineRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:300'],
        ];
    }
}
