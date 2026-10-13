<?php

namespace App\Actions\Users;

use App\Models\AirlineMembership;
use App\Models\User;
use App\Support\ActivityLevel;
use Illuminate\Support\Collection;

/**
 * Deletes a user account. Shared by self-deletion (/settings/danger) and the
 * admin user page; callers check blockingAirlines() first.
 */
class DeleteUser
{
    /**
     * Airlines that would be left without an owner or without any manager if
     * the user were deleted. Deletion must be refused while this is non-empty.
     */
    public function blockingAirlines(User $user): Collection
    {
        $soleManaged = $user->airlines()
            ->wherePivot('role', 'Manager')
            ->get()
            ->filter(fn ($airline) => AirlineMembership::where('airline_id', $airline->id)
                ->where('role', 'Manager')
                ->count() <= 1);

        return $user->ownedAirlines()->get()
            ->merge($soleManaged)
            ->unique('id')
            ->values();
    }

    /**
     * @param  User|null  $causer  The admin deleting the account, or null for self-deletion.
     */
    public function __invoke(User $user, ?User $causer = null): void
    {
        // Record the deletion before the user row disappears; capture identity
        // in the properties so the audit entry stays meaningful.
        activity()
            ->causedBy($causer ?? $user)
            ->withProperties([
                'level' => $causer ? ActivityLevel::WARNING : ActivityLevel::INFO,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->event($causer ? 'user_deleted_by_admin' : 'account_deleted')
            ->log($causer ? 'Deleted user account' : 'Deleted own account');

        // Clean up user-keyed rows the database won't cascade on its own.
        $user->tokens()->delete();
        $user->notifications()->delete();
        $user->syncRoles([]);

        // Flights are anonymized (pilot_id -> NULL) via the FK; memberships,
        // NOTAMs and invite codes cascade automatically.
        $user->delete();
    }
}
