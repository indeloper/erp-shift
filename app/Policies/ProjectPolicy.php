<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
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

    public function edit(User $user, Project $project)
    {
        return $project->user_id == $user->id or
            in_array($user->id, $project->respUsers()->pluck('user_id')->toArray()) or
            in_array($user->group_id, [5, 6]) or
            $user->can('projects_responsible_users');
    }

    public function viewAny(User $user): bool
    {
        return true;
    }
}
