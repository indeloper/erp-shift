<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BitrixEventRequest extends FormRequest
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
            'event'                => 'required|string',
            'event_id'             => 'required|integer',
            'data'                 => 'required|array',
            'data.FIELDS_AFTER'    => 'required|array',
            'data.FIELDS_AFTER.ID' => 'required|int',
        ];
    }

}
