<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Only an active aircraft owned by the active airline can be assigned to a
 * flight - retired/inactive/foreign airframes are refused at filing.
 */
class AircraftAvailabilityTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected Airline $airline;

    protected User $pilot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        $this->airline = Airline::factory()->create();
        $this->pilot = $this->memberOf($this->airline, 'Pilot');
    }

    private function fileWith(Aircraft $aircraft)
    {
        return $this->actingAs($this->pilot)
            ->withSession(['activeairline' => $this->airline])
            ->post(route('flightadd'), [
                'flightnumber' => 421,
                'departure_icao' => 'EDDF',
                'arrival_icao' => 'EGLL',
                'aircraft_id' => $aircraft->id,
                'callsign' => '421',
                'crzalt' => 35000,
                'blockoff' => '2026-07-11 10:00:00',
                'blockon' => '2026-07-11 11:30:00',
                'burned_fuel' => 5000,
                'route' => 'DCT',
                'online_network_id' => 1,
            ]);
    }

    public function test_a_retired_aircraft_cannot_be_flown(): void
    {
        $aircraft = Aircraft::factory()->retired()->create(['used_by' => $this->airline->id]);

        $this->fileWith($aircraft)->assertSessionHasErrors('aircraft_id');
        $this->assertDatabaseCount('flights', 0);
    }

    public function test_an_inactive_aircraft_cannot_be_flown(): void
    {
        $aircraft = Aircraft::factory()->inactive()->create(['used_by' => $this->airline->id]);

        $this->fileWith($aircraft)->assertSessionHasErrors('aircraft_id');
        $this->assertDatabaseCount('flights', 0);
    }

    public function test_an_aircraft_from_another_airline_cannot_be_flown(): void
    {
        $otherAirline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $otherAirline->id]);

        $this->fileWith($aircraft)->assertSessionHasErrors('aircraft_id');
        $this->assertDatabaseCount('flights', 0);
    }
}
