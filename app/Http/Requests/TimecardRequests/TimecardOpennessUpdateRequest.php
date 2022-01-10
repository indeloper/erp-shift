<?php

namespace App\Http\Requests\TimecardRequests;

use App\Models\HumanResources\Timecard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TimecardOpennessUpdateRequest extends FormRequest
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
        if ($this->is_opened === $timecard->is_opened) {
            $type = $this->is_opened === 1 ?
                ['already_opened', 'Данный табель уже открыт, его нельзя открыть ещё раз'] :
                ['already_closed', 'Данный табель уже закрыт, его нельзя закрыть ещё раз'];
            $validator->errors()->add($type[0], $type[1]);
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
            'is_opened' => ['required', 'integer', 'in:0,1'],
            'user_id' => ['required', 'exists:users,id'],
            'timecard_id' => ['required', 'exists:timecards,id'],
        ];
    }
}
