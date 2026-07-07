<?php

namespace App\Policies;

use App\Models\Flight;
use App\Models\User;

/**
 * API-safe flight authorization - unlike AircraftPolicy this must not read
 * session('activeairline'), which is empty under stateless Sanctum auth.
 */
class FlightPolicy
{
    /**
     * Accept or reject a PIREP. Same rule as the web review pages: the
     * per-airline Dispatcher/Manager role, and reviewers cannot action their
     * own PIREP unless they are a Manager of the airline. Super-Admin passes
     * everything via the Gate::before hook in AuthServiceProvider.
     */
    public function review(User $user, Flight $flight): bool
    {
        if (! $user->canReviewFlightsFor($flight->airline)) {
            return false;
        }

        if ($flight->pilot_id === $user->id && ! $user->isManagerOf($flight->airline)) {
            return false;
        }

        return true;
    }
}
