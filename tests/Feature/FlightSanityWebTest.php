<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Physical sanity guards on the web PIREP filing path (parity with the API in
 * tests/Feature/Api/FlightApiTest.php): fuel must be positive and within a
 * ceiling, block-on must be after block-off, and the duration is capped.
 */
class FlightSanityWebTest extends TestCase
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
    private function airlineWithAircraft(): array
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        return [$airline, $aircraft, $pilot];
    }

    private function payload(Aircraft $aircraft, array $overrides = []): array
    {
        return $overrides + [
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
        ];
    }

    private function file(User $pilot, Airline $airline, array $payload)
    {
        return $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightadd'), $payload);
    }

    public function test_non_positive_fuel_is_rejected(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraft();

        $this->file($pilot, $airline, $this->payload($aircraft, ['burned_fuel' => 0]))
            ->assertSessionHasErrors('burned_fuel');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_absurd_fuel_is_rejected(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraft();

        $this->file($pilot, $airline, $this->payload($aircraft, ['burned_fuel' => 9_000_000]))
            ->assertSessionHasErrors('burned_fuel');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_block_on_before_block_off_is_rejected(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraft();

        $this->file($pilot, $airline, $this->payload($aircraft, [
            'blockoff' => '2026-07-11 11:30:00',
            'blockon' => '2026-07-11 10:00:00',
        ]))->assertSessionHasErrors('blockon');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_flight_longer_than_the_max_duration_is_rejected(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraft();

        // 27 hours, above the 26-hour cap.
        $this->file($pilot, $airline, $this->payload($aircraft, [
            'blockoff' => '2026-07-11 10:00:00',
            'blockon' => '2026-07-12 13:00:00',
        ]))->assertSessionHasErrors('blockon');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_a_flight_in_the_future_is_rejected(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraft();

        $off = now('UTC')->addDays(2);
        $on = $off->copy()->addMinutes(90);

        $this->file($pilot, $airline, $this->payload($aircraft, [
            'blockoff' => $off->format('Y-m-d H:i:s'),
            'blockon' => $on->format('Y-m-d H:i:s'),
        ]))->assertSessionHasErrors('blockon');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_cruise_altitude_above_the_service_ceiling_is_rejected(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->serviceCeiling(41000)->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->file($pilot, $airline, $this->payload($aircraft, ['crzalt' => 45000]))
            ->assertSessionHasErrors('crzalt');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_a_valid_flight_is_still_accepted(): void
    {
        [$airline, $aircraft, $pilot] = $this->airlineWithAircraft();

        $this->file($pilot, $airline, $this->payload($aircraft))
            ->assertRedirect(route('flightlist'));

        $this->assertDatabaseCount('flights', 1);
    }
}
