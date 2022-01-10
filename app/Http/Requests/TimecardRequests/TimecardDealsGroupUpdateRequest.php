<?php

namespace App\Http\Requests\TimecardRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TimecardDealsGroupUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return (bool) auth()->user()->hasPermission('human_resources_timecard_fill');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    public function withValidator($validator)
    {
        if (! $this->timecard->is_opened) {
            $validator->errors()->add('already_closed', 'Данный табель закрыт, ему нельзя менять сделки');
            throw new ValidationException($validator);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'old_tariff' => ['required', 'numeric'],
            'new_tariff' => ['sometimes', 'nullable', 'numeric'],
            'old_length' => ['required', 'numeric'],
            'new_length' => ['sometimes', 'nullable', 'numeric'],
        ];
    }
}
