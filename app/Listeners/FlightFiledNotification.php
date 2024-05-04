<?php

namespace App\Listeners;

use App\Events\FlightFiled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FlightFiledNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        dd('Notification trigger');
    }

    /**
     * Handle the event.
     */
    public function handle(FlightFiled $event): void
    {
        dd('Notification trigger');
    }
}
