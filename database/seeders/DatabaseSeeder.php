<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            //UserSeeder::class,
            OnlineNetworksSeeder::class,
            AirportsSeeder::class,
            AirlinesSeeder::class,
            AircraftSeeder::class,
            UserAndPermissionsSeeder::class,
            AirlineMembershipSeeder::class,
            FlightStatusSeeder::class,
            FlightsSeeder::class
        ]);
    }
}
