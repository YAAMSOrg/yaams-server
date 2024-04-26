<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Events\FlightFiled;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(
            FlightFiled::class,
        );
    }
}
