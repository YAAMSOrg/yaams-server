<?php

namespace App\Policies;

use App\Models\Airline;
use App\Models\Setting;
use App\Models\User;

/**
 * Single source of the per-airline permissions, used by the web controllers,
 * the API and the `abilities` the API reports to clients
 * (App\Support\Abilities). Session-free, so it works under stateless Sanctum
 * auth. Super-Admin passes everything via the Gate::before hook in
 * AuthServiceProvider.
 *
 * Suspension is not checked here - the `airline.writable` middleware refuses
 * every write of a suspended airline.
 */
class AirlinePolicy
{
    /**
     * Found a new airline.
     */
    public function create(User $user): bool
    {
        return Setting::get('allow_user_airline_creation') === '1';
    }

    /**
     * File PIREPs - any member.
     */
    public function fileFlights(User $user, Airline $airline): bool
    {
        return $user->isMemberOf($airline);
    }

    /**
     * Open the review queue and accept/reject PIREPs (per-flight limits in
     * FlightPolicy::review()).
     */
    public function reviewFlights(User $user, Airline $airline): bool
    {
        return $user->canReviewFlightsFor($airline);
    }

    public function addAircraft(User $user, Airline $airline): bool
    {
        return $user->isManagerOf($airline);
    }

    /**
     * Edit or retire aircraft (per-aircraft limits in AircraftPolicy).
     */
    public function editAircraft(User $user, Airline $airline): bool
    {
        return $user->isManagerOf($airline);
    }

    public function manageMembers(User $user, Airline $airline): bool
    {
        return $user->isManagerOf($airline);
    }

    public function manageInviteCodes(User $user, Airline $airline): bool
    {
        return $user->isManagerOf($airline);
    }

    public function manageNotams(User $user, Airline $airline): bool
    {
        return $user->isManagerOf($airline);
    }

    public function editSettings(User $user, Airline $airline): bool
    {
        return $user->isManagerOf($airline);
    }
}
