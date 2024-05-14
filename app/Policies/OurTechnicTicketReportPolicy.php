<?php

namespace App\Policies;

use App\Models\TechAcc\OurTechnicTicketReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OurTechnicTicketReportPolicy
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

    public function store(User $user)
    {
        return true;
    }

    public function update(User $user, OurTechnicTicketReport $report)
    {
        return true;
        // return true;
    }

    public function destroy(User $user, OurTechnicTicketReport $report)
    {
        return true;

        // return $user->id === $report->ticket->users()->wherePivot('type', 4);
    }
}
