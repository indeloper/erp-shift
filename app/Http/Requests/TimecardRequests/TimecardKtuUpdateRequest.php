<?php

namespace App\Http\Requests\TimecardRequests;

use App\Models\HumanResources\Timecard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TimecardKtuUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return (bool) auth()->user()->hasPermission('human_resources_timecard_management');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }

    public function withValidator($validator)
    {
        $timecard = Timecard::find($this->timecard_id);
        if (! $timecard->is_opened) {
            $validator->errors()->add('already_closed', 'Данный табель закрыт, ему нельзя менять КТУ');
            throw new ValidationException($validator);
        }
    }

    /**
     * Get the validation rules fail messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'ktu.between' => 'КТУ изменяется в пределах от 0 до 100',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'ktu' => ['required', 'integer', 'between:0,100'],
            'user_id' => ['required', 'exists:users,id'],
            'timecard_id' => ['required', 'exists:timecards,id'],
        ];
    }
}
