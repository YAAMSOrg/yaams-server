<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Flight>
 *
 * Assumes reference data (flight_statuses, online_networks) is present -
 * feature tests seed it via the SeedsDomain trait.
 */
class FlightFactory extends Factory
{
    protected $model = Flight::class;

    public function definition(): array
    {
        $airline = Airline::factory();

        return [
            'airline_id' => $airline,
            // Keep the airframe in the same airline as the flight.
            'aircraft_id' => fn (array $attributes) => Aircraft::factory()
                ->create(['used_by' => $attributes['airline_id']])->id,
            'pilot_id' => User::factory(),
            'callsign' => '421',
            'flightnumber' => 421,
            'departure_icao' => fn () => Airport::factory()->create()->icao_code,
            'arrival_icao' => fn () => Airport::factory()->create()->icao_code,
            'crzalt' => 35000,
            'blockoff' => '2026-07-11 10:00:00',
            'blockon' => '2026-07-11 11:30:00', // 90 minutes
            'burned_fuel' => 5000,
            'route' => 'DCT',
            'online_network_id' => 1,
            'status_id' => 1, // Pending
            'remarks' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['status_id' => 2]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status_id' => 3]);
    }
}
