<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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

    public function card(User $me, User $user)
    {
        return ($user->id == $me->id) or $me->can('users');
    }

    public function update(User $me, User $user)
    {
        return ($user->id == $me->id) or $me->can('users_edit');
    }

    public function viewAny(User $user)
    {
        return true;
    }
}
