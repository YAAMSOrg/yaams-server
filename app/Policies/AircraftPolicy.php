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
        // A retired aircraft is permanently locked and can never be edited or reactivated.
        return $activeAirline
            && $aircraft->ownedBy($activeAirline)
            && $user->can('editAircraft', $activeAirline)
            && !$aircraft->isRetired();
    }

    /**
     * Determine whether the user can upload a screenshot for the aircraft.
     *
     * Any member of the owning airline may contribute (the upload lands in a
     * pending state for manager review). Membership is implied by the aircraft
     * being owned by the user's active airline - they can only make an airline
     * active if they belong to it. Retired aircraft accept no uploads.
     */
    public function uploadImage(User $user, Aircraft $aircraft): bool
    {
        $activeAirline = session('activeairline');
        return $activeAirline
            && $aircraft->ownedBy($activeAirline)
            && !$aircraft->isRetired();
    }

    /**
     * Determine whether the user can moderate the aircraft's screenshots
     * (approve, reject, delete any, set the main shot).
     *
     * Unlike `update`, this holds for retired aircraft too: shots submitted
     * before retirement must not be stranded in an unreachable review queue.
     * Retired aircraft still accept no new uploads (see `uploadImage`).
     */
    public function moderateImages(User $user, Aircraft $aircraft): bool
    {
        $activeAirline = session('activeairline');
        return $activeAirline
            && $aircraft->ownedBy($activeAirline)
            && $user->can('editAircraft', $activeAirline);
    }

    /**
     * Determine whether the user can retire (permanently remove) the aircraft.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Aircraft  $aircraft
     * @return bool
     */
    public function retire(User $user, Aircraft $aircraft): bool
    {
        $activeAirline = session('activeairline');
        return $activeAirline
            && $aircraft->ownedBy($activeAirline)
            && $user->can('editAircraft', $activeAirline)
            && !$aircraft->isRetired();
    }
}
