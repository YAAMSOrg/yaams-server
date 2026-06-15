<?php

namespace App\Policies;

use App\Models\Aircraft;
use App\Models\User;

class AircraftPolicy
{
    /**
     * Determine whether the user can view the aircraft.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Aircraft  $aircraft
     * @return bool
     */
    public function view(User $user, Aircraft $aircraft): bool
    {
        $activeAirline = session('activeairline');
        return $activeAirline && $aircraft->ownedBy($activeAirline);
    }

    /**
     * Determine whether the user can update the aircraft.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Aircraft  $aircraft
     * @return bool
     */
    public function update(User $user, Aircraft $aircraft): bool
    {
        $activeAirline = session('activeairline');
        return $user->can('edit aircraft') && $activeAirline && $aircraft->ownedBy($activeAirline);
    }
}
