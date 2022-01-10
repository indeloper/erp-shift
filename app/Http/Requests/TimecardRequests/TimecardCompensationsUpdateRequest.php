<?php

namespace App\Http\Requests\TimecardRequests;

use App\Models\HumanResources\Timecard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TimecardCompensationsUpdateRequest extends FormRequest
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
            $validator->errors()->add('already_closed', 'Данный табель закрыт, ему нельзя менять список компенсаций');
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
            'compensations' => ['nullable', 'array', 'max:20'],
            'compensations.*.name' => ['required', 'string', 'max:500'],
            'compensations.*.amount' => ['required', 'numeric'],
            'compensations.*.prolonged' => ['sometimes'],
            'deleted_compensations' => ['nullable', 'array'],
            'deleted_compensations.*' => ['required', 'exists:timecard_additions,id'],
            'user_id' => ['required', 'exists:users,id'],
            'timecard_id' => ['required', 'exists:timecards,id'],
        ];
    }
}
