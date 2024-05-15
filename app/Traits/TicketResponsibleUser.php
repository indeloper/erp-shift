<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait TicketResponsibleUser
{
    public $ticket_responsible_types = [
        1 => 'resp_rp_user_id',
        2 => 'request_resp_user_id',
        3 => 'recipient_user_id',
        4 => 'usage_resp_user_id',
        5 => 'author_user_id',
        6 => 'process_resp_user_id',
    ];

    public $main_logist_id = 1; //this s hardcoded cuz there are two logists in SK

    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $human_type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType(Builder $query, $human_type): Builder
    {
        $type = array_search($human_type, $this->ticket_responsible_types);

        return $query->where('type', $type);
    }

    public function scopeActiveResp($q)
    {
        return $q->where('deactivated_at', null);
    }
}
