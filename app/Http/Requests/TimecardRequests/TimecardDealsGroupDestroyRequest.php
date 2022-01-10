<?php

namespace App\Http\Requests\TimecardRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property integer timecard_id
 * @property integer tariff_id
 * @property integer length
 */
class TimecardDealsGroupDestroyRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'timecard_id' => 'required',
            'tariff_id' => 'required',
            'length' => 'required'
        ];
    }
}
