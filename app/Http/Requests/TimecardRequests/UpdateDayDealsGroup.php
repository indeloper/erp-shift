<?php

namespace App\Http\Requests\TimecardRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDayDealsGroup extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return (bool) auth()->user()->hasPermission('human_resources_timecard_fill');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'project_id' => 'required',
            'day' => 'required',
            'old_tariff' => ['required', 'numeric'],
            'new_tariff' => ['sometimes', 'nullable', 'numeric'],
            'old_length' => ['required', 'numeric'],
            'new_length' => ['sometimes', 'nullable', 'numeric'],
        ];
    }
}
