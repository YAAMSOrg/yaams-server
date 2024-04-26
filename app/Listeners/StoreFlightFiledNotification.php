<?php

namespace App\Listeners;

use App\Events\FlightFiled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreFlightFiledNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FlightFiled $event): void
    {
        Log::info('This is some useful information.');
    }
}
