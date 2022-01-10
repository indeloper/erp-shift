<?php

namespace App\Http\Requests\TimecardRequests;

use App\Models\HumanResources\TimecardDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TimecardDayDealsUpdateRequest extends FormRequest
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
            'deals' => ['nullable', 'array', 'max:20'],
            'deals.*.tariff_id' => ['required', 'exists:tariff_rates,id'],
            'deals.*.length' => ['required', 'numeric'],
            'deals.*.amount' => ['required', 'numeric'],
            'deals.*.type' => ['sometimes', 'in:3'],
            'deleted_addition_ids' => ['nullable', 'array'],
            'deleted_addition_ids.*' => ['required', 'exists:timecard_records,id'],
            'user_id' => ['required', 'exists:users,id'],
            'timecard_day_id' => ['required', 'exists:timecard_days,id'],
        ];
    }
}
