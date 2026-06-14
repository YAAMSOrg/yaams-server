<?php

namespace App\Listeners;

use App\Events\FlightFiled;
use App\Models\Notification;
use App\Models\User;

class FlightFiledNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(FlightFiled $event): void
    {
        $flight = $event->flight;
        $airline = $flight->airline;
        $pilot = $flight->pilot;

        // Find all users in this airline who have the 'review flight' permission
        $reviewers = $airline->users()
            ->whereIn('users.id', User::permission('review flight')->pluck('id'))
            ->get();

        foreach ($reviewers as $reviewer) {
            // Don't notify the pilot who filed the flight themselves, even if they are a reviewer
            if ($reviewer->id === $pilot->id) {
                continue;
            }

            Notification::create([
                'title' => 'New PIREP to review',
                'message' => "Pilot {$pilot->name} filed a new flight ({$flight->full_flight_number}) from {$flight->departure_icao} to {$flight->arrival_icao}.",
                'url' => route('viewflight', $flight->id),
                'target_id' => $reviewer->id,
                'acknowledged' => false,
            ]);
        }
    }
}
