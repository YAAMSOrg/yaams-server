<?php

namespace App\Listeners;

use App\Events\FlightFiled;
use App\Notifications\PirepFiled;
use Illuminate\Support\Facades\Notification;

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

        // Find all Dispatchers and Managers in this airline, except the pilot
        // who filed the flight themselves (even if they are a reviewer).
        $reviewers = $airline->users()
            ->wherePivotIn('role', ['Dispatcher', 'Manager'])
            ->where('users.id', '!=', $pilot->id)
            ->get();

        // Which channels each reviewer is reached on lives in the notification's
        // via() method — currently in-app + email.
        Notification::send($reviewers, new PirepFiled($flight));
    }
}
