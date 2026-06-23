<?php

namespace App\Listeners;

use App\Events\FlightFiled;
use App\Models\Notification;

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

        // Find all Dispatchers and Managers in this airline
        $reviewers = $airline->users()
            ->wherePivotIn('role', ['Dispatcher', 'Manager'])
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
