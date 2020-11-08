<?php

namespace App\Http\Requests\Building\TechAccounting\OurTechnic\OurTechnicTicketReportRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOurTechnicTicketReportRequest extends FormRequest
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
            'hours' => 'required|numeric|between:0,24',
            'comment' => 'required_if:hours,0|max:1000',
        ];
    }
}
