<?php

namespace App\Policies;

use App\Models\TechAcc\OurTechnicTicket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OurTechnicTicketActionsPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function close(User $user, OurTechnicTicket $ticket)
    {
        return ($ticket->users()->wherePivot('type', 4)->activeResp()->get()->pluck('id')->contains($user->id) or $user->isProjectManager()) && in_array($ticket->status, [5, 6, 7]);
    }

    public function request_extension(User $user, OurTechnicTicket $ticket)
    {
        return ($ticket->users()->wherePivot('type', 4)->activeResp()->get()->pluck('id')->contains($user->id) or $user->isProjectManager()) && in_array($ticket->status, [5, 6, 7]);
    }

    public function agree_extension(User $user, OurTechnicTicket $ticket)
    {
        // TODO need check status in work
        return ($user->id == $ticket->users()->wherePivot('type', 1)->first()->id or $user->isProjectManager()) && in_array($ticket->status, [5, 6, 7]);
    }
}
