<?php

namespace App\Http\Requests;

use App\Models\TechAcc\OurTechnicTicket;
use Illuminate\Foundation\Http\FormRequest;

class TicketStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', OurTechnicTicket::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'our_technic_id' => 'required',
            'resp_rp_user_id' => 'required',
            'recipient_user_id' => 'sometimes|required_with:getting_from_date',
            'usage_resp_user_id' => 'sometimes|required_with:usage_from_date',

            'sending_object_id' => 'sometimes|nullable|required_with:recipient_user_id',
            'getting_object_id' => 'required',

            'getting_to_date' => 'sometimes|nullable|date|after_or_equal:today',

            'usage_from_date' => 'sometimes|nullable|date|after_or_equal:sending_from_date',
            'usage_to_date' => 'sometimes|nullable|required_with:usage_from_date|date|after_or_equal:usage_from_date',
        ];
    }
}
