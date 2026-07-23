<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Location-continuity realism mode: the airframe must depart from where it
 * currently sits, moves on filing, and is reverted when a pending PIREP is
 * rejected (unless a later leg already moved it on).
 */
class LocationContinuityTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    /**
     * @return array{0: Airline, 1: Aircraft, 2: User}
     */
    private function airlineWithAircraftAt(string $icao): array
    {
        $airline = Airline::factory()->locationContinuity()->create();
        $aircraft = Aircraft::factory()->at($icao)->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        return [$airline, $aircraft, $pilot];
    }

    private function filePayload(Aircraft $aircraft, string $from, string $to): array
    {
        return [
            'flightnumber' => 421,
            'departure_icao' => $from,
            'arrival_icao' => $to,
            'aircraft_id' => $aircraft->id,
            'callsign' => '421',
            'crzalt' => 35000,
            'blockoff' => '2026-07-11 10:00:00',
            'blockon' => '2026-07-11 11:30:00',
            'burned_fuel' => 5000,
            'route' => 'DCT',
            'online_network_id' => 1,
        ];
    }

    public function test_pirep_is_rejected_when_departure_does_not_match_aircraft_location(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraftAt('EDDF');

        $response = $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightadd'), $this->filePayload($aircraft, 'EDDM', 'EGLL'));

        $response->assertSessionHasErrors('departure_icao');
        $this->assertDatabaseCount('flights', 0);
        $this->assertSame('EDDF', $aircraft->fresh()->current_loc);
    }

    public function test_filing_moves_the_aircraft_to_the_arrival_airport(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraftAt('EDDF');

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightadd'), $this->filePayload($aircraft, 'EDDF', 'EGLL'))
            ->assertRedirect(route('flightlist'));

        $this->assertDatabaseCount('flights', 1);
        $this->assertSame('EGLL', $aircraft->fresh()->current_loc);
    }

    public function test_rejecting_a_pending_pirep_reverts_the_aircraft_to_departure(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraftAt('EDDF');
        $manager = $this->memberOf($airline, 'Manager');

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightadd'), $this->filePayload($aircraft, 'EDDF', 'EGLL'));

        $flight = Flight::firstOrFail();
        $this->assertSame('EGLL', $aircraft->fresh()->current_loc);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightreviewreject', $flight));

        $this->assertSame('EDDF', $aircraft->fresh()->current_loc);
    }

    public function test_rejection_does_not_revert_when_a_later_leg_already_moved_the_aircraft(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraftAt('EDDF');
        $manager = $this->memberOf($airline, 'Manager');

        // Leg 1: EDDF -> EGLL (aircraft now at EGLL)
        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightadd'), $this->filePayload($aircraft, 'EDDF', 'EGLL'));
        $legOne = Flight::firstOrFail();

        // Leg 2: EGLL -> KJFK (aircraft now at KJFK)
        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightadd'), $this->filePayload($aircraft, 'EGLL', 'KJFK'));

        $this->assertSame('KJFK', $aircraft->fresh()->current_loc);

        // Rejecting leg 1 must not drag the airframe back - it has moved on.
        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightreviewreject', $legOne));

        $this->assertSame('KJFK', $aircraft->fresh()->current_loc);
    }

    /**
     * A manager may edit an aircraft (needs the per-airline Manager role for the
     * policy and the Spatie `edit aircraft` permission for the route middleware).
     */
    private function managerOf(Airline $airline): User
    {
        $manager = $this->memberOf($airline, 'Manager');
        $manager->givePermissionTo(Permission::findOrCreate('edit aircraft', 'web'));

        return $manager;
    }

    private function editPayload(Aircraft $aircraft, string $currentLoc): array
    {
        return [
            'registration' => $aircraft->registration,
            'manufacturer' => 'Airbus',
            'model' => 'A320',
            'engine_type' => 'CFM56',
            'current_loc' => $currentLoc,
            'active' => 1,
        ];
    }

    public function test_manager_can_override_aircraft_location_while_continuity_is_active(): void
    {
        [$airline, $aircraft] = $this->airlineWithAircraftAt('EDDF');
        $manager = $this->managerOf($airline);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->post(route('editaircraft', $aircraft), $this->editPayload($aircraft, 'KLAX'))
            ->assertRedirect(route('fleetmanager'));

        $this->assertSame('KLAX', $aircraft->fresh()->current_loc);
    }

    public function test_overriding_the_location_with_an_unknown_airport_is_rejected(): void
    {
        [$airline, $aircraft] = $this->airlineWithAircraftAt('EDDF');
        $manager = $this->managerOf($airline);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->post(route('editaircraft', $aircraft), $this->editPayload($aircraft, 'ZZZZ'))
            ->assertSessionHasErrors('current_loc');

        $this->assertSame('EDDF', $aircraft->fresh()->current_loc);
    }
}
