<?php

namespace Tests\Feature\Api;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * API v1 PIREP endpoints - mirrors the web filing/review rules (Sanctum auth,
 * membership, location continuity, review authorization).
 */
class FlightApiTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    private function payload(Aircraft $aircraft, string $from = 'EDDF', string $to = 'EGLL'): array
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

    public function test_filing_requires_authentication(): void
    {
        $airline = Airline::factory()->create();

        $this->postJson(route('api.v1.airlines.flights.store', $airline), [])
            ->assertUnauthorized();
    }

    public function test_a_member_can_file_a_pirep(): void
    {
        Notification::fake();

        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        Sanctum::actingAs($pilot);

        $this->postJson(route('api.v1.airlines.flights.store', $airline), $this->payload($aircraft))
            ->assertCreated();

        $this->assertDatabaseHas('flights', [
            'airline_id' => $airline->id,
            'pilot_id' => $pilot->id,
            'departure_icao' => 'EDDF',
            'arrival_icao' => 'EGLL',
        ]);
    }

    public function test_non_positive_fuel_is_rejected(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        Sanctum::actingAs($pilot);

        $this->postJson(
            route('api.v1.airlines.flights.store', $airline),
            ['burned_fuel' => 0] + $this->payload($aircraft),
        )
            ->assertStatus(422)
            ->assertJsonValidationErrors('burned_fuel');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_absurd_fuel_is_rejected(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        Sanctum::actingAs($pilot);

        $this->postJson(
            route('api.v1.airlines.flights.store', $airline),
            ['burned_fuel' => 9_000_000] + $this->payload($aircraft),
        )
            ->assertStatus(422)
            ->assertJsonValidationErrors('burned_fuel');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_block_on_before_block_off_is_rejected(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        Sanctum::actingAs($pilot);

        $this->postJson(route('api.v1.airlines.flights.store', $airline), [
            'blockoff' => '2026-07-11 11:30:00',
            'blockon' => '2026-07-11 10:00:00',
        ] + $this->payload($aircraft))
            ->assertStatus(422)
            ->assertJsonValidationErrors('blockon');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_flight_longer_than_the_max_duration_is_rejected(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        Sanctum::actingAs($pilot);

        // 27 hours, above the 26-hour cap.
        $this->postJson(route('api.v1.airlines.flights.store', $airline), [
            'blockoff' => '2026-07-11 10:00:00',
            'blockon' => '2026-07-12 13:00:00',
        ] + $this->payload($aircraft))
            ->assertStatus(422)
            ->assertJsonValidationErrors('blockon');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_location_continuity_is_enforced_on_the_api(): void
    {
        $airline = Airline::factory()->locationContinuity()->create();
        $aircraft = Aircraft::factory()->at('EDDF')->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        Sanctum::actingAs($pilot);

        // Aircraft is at EDDF, so departing EDDM must be a 422.
        $this->postJson(route('api.v1.airlines.flights.store', $airline), $this->payload($aircraft, 'EDDM', 'EGLL'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('departure_icao');

        $this->assertDatabaseCount('flights', 0);
    }

    public function test_a_non_reviewer_cannot_accept_a_pirep(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');
        $flight = Flight::factory()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'pilot_id' => $pilot->id,
            'status_id' => 1,
        ]);

        Sanctum::actingAs($pilot);

        $this->postJson(route('api.v1.flights.accept', $flight))
            ->assertForbidden();

        $this->assertSame(1, $flight->fresh()->status_id);
    }
}
