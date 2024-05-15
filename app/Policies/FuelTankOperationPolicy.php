<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\TechAcc\FuelTank\FuelTankOperation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FuelTankOperationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any fuel tank operations.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the fuel tank operation.
     *
     * @return mixed
     */
    public function view(User $user, FuelTankOperation $fuelTankOperation)
    {
        return true;
    }

    /**
     * Determine whether the user can create fuel tank operations.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        $user_groups = array_merge(Group::FOREMEN, Group::PROJECT_MANAGERS, [47]);

        return in_array($user->group_id, $user_groups);
    }

    /**
     * Determine whether the user can update the fuel tank operation.
     *
     * @return mixed
     */
    public function update(User $user, FuelTankOperation $fuelTankOperation)
    {
        $user_groups = array_merge(Group::FOREMEN, Group::PROJECT_MANAGERS, [47]);

        return in_array($user->group_id, $user_groups);
    }

    /**
     * Determine whether the user can delete the fuel tank operation.
     *
     * @return mixed
     */
    public function delete(User $user, FuelTankOperation $fuelTankOperation)
    {
        $user_groups = array_merge(Group::PROJECT_MANAGERS, [47]);

        return in_array($user->group_id, $user_groups);
    }

    /**
     * Determine whether the user can restore the fuel tank operation.
     *
     * @return mixed
     */
    public function restore(User $user, FuelTankOperation $fuelTankOperation)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the fuel tank operation.
     *
     * @return mixed
     */
    public function forceDelete(User $user, FuelTankOperation $fuelTankOperation)
    {
        //
    }
}
