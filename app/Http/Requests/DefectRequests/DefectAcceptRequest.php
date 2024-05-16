<?php

namespace App\Http\Requests\DefectRequests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class DefectAcceptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->repair_start_date and $this->repair_end_date) {
            $this->merge([
                'repair_start_date' => Carbon::createFromFormat('d.m.Y', $this->repair_start_date),
                'repair_end_date' => Carbon::createFromFormat('d.m.Y', $this->repair_end_date),
                'now' => now(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'comment' => ['required', 'string', 'max:300'],
            'repair_start_date' => 'required|date|'.($this->change_end_date ? '' : 'after_or_equal:now'),
            'repair_end_date' => ['required', 'date', 'after_or_equal:repair_start_date'],
        ];
    }
}
