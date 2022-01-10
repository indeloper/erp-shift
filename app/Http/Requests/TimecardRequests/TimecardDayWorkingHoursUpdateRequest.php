<?php

namespace App\Http\Requests\TimecardRequests;

use App\Models\HumanResources\TimecardDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TimecardDayWorkingHoursUpdateRequest extends FormRequest
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
        $timecard = TimecardDay::findOrFail($this->timecard_day_id)->timecard;
        if (! $timecard->is_opened) {
            $validator->errors()->add('already_closed', 'Данный табель закрыт, ему нельзя менять рабочее время');
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
            'working_hours' => ['nullable', 'array', 'max:20'],
            'working_hours.*.tariff_id' => ['required', 'exists:tariff_rates,id'],
            'working_hours.*.amount' => ['required', 'numeric'],
            'working_hours.*.type' => ['sometimes', 'in:2'],
            'deleted_addition_ids' => ['nullable', 'array'],
            'deleted_addition_ids.*' => ['required', 'exists:timecard_records,id'],
            'user_id' => ['required', 'exists:users,id'],
            'timecard_day_id' => ['required', 'exists:timecard_days,id'],
        ];
    }
}
