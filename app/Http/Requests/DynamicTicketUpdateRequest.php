<?php

namespace App\Http\Requests;

use App\Services\TechAccounting\TechnicTicketService;
use Illuminate\Foundation\Http\FormRequest;

class DynamicTicketUpdateRequest extends FormRequest
{
    protected $ticket_status_rules_map = [
        1 => [
            'acceptance' => 'required|in:confirm,reject',
        ],
        2 => [
            'result' => 'required|in:confirm,reject,hold',
            'sending_from_date' => 'sometimes|date|after_or_equal:today',
            'sending_to_date' => 'sometimes|required_with:sending_from_date|date|after_or_equal:sending_from_date',
            'getting_from_date' => 'sometimes|required_with:getting_from_date|date|after_or_equal:getting_from_date',
            'getting_to_date' => 'sometimes|required_with:getting_to_date|date|after_or_equal:getting_to_date',
            'ticket_resp_user_id' => 'sometimes|required_with:sending_object_id',
        ],
        4 => [
            'result' => 'required|in:confirm,reject',
            'sending_from_date' => 'sometimes|date|after_or_equal:today',
            'sending_to_date' => 'sometimes|required_with:sending_from_date|date|after_or_equal:sending_from_date',
            'getting_from_date' => 'sometimes|required_with:getting_from_date|date|after_or_equal:getting_from_date',
            'getting_to_date' => 'sometimes|required_with:getting_to_date|date|after_or_equal:getting_to_date',
            'our_technic_id' => 'sometimes|required',
            'ticket_resp_user_id' => 'sometimes|required_with:sending_object_id',
        ],
        5 => [
            'result' => 'required|in:confirm',
        ],
        6 => [
            'file_ids' => 'sometimes|array',
            'task_status' => 'sometimes|in:31,32',
            'result' => 'sometimes|in:confirm,rollback,update',
        ],
    ];


    /**
     * Determine if the user is authorized to make this request.
     *
     * @param TechnicTicketService $service
     * @return bool
     */
    public function authorize(TechnicTicketService $service)
    {
        $ticket = $this->route('our_technic_ticket');
        $curr_type = $service->ticket_status_responsible_user_map[$ticket->status];

        return $service->isAuthIsTicketRespOfType($ticket, $curr_type);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $ticket = $this->route('our_technic_ticket');
        return $this->ticket_status_rules_map[$ticket->status];
    }
}
