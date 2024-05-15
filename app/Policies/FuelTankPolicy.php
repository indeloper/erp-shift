<?php

namespace App\Policies;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FuelTankPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create fuel tanks.
     *
     * @param  \App\Models\TechAcc\FuelTank\FuelTank  $fuelTank
     * @return mixed
     */
    public function store(User $user)
    {
        // rp and mehanic
        return in_array($user->group_id, [8, 27, 13, 19, 47]);
    }

    /**
     * Determine whether the user can update the fuel tank.
     *
     * @return mixed
     */
    public function update(User $user, FuelTank $fuelTank): bool
    {
        // rp and mehanic
        return in_array($user->group_id, [8, 27, 13, 19, 47]);
    }

    /**
     * Determine whether the user can delete the fuel tank.
     *
     * @return mixed
     */
    public function destroy(User $user, FuelTank $fuelTank)
    {
        // rp and mehanic
        return in_array($user->group_id, [15]) || $user->id == $user->main_logist_id;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }
}
