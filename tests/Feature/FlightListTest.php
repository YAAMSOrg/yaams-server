<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * The pilot's logbook (flight list): own flights only, filterable by status
 * name, with per-status counts and accepted block time in the header.
 */
class FlightListTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_status_filter_counts_and_block_time(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $colleague = $this->memberOf($airline, 'Pilot');

        // 90 min accepted + 45 min accepted, one pending, one rejected
        Flight::factory()->accepted()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        Flight::factory()->accepted()->create([
            'airline_id' => $airline->id, 'pilot_id' => $pilot->id,
            'blockoff' => '2026-07-12 10:00:00', 'blockon' => '2026-07-12 10:45:00',
        ]);
        Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        Flight::factory()->rejected()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        Flight::factory()->accepted()->create(['airline_id' => $airline->id, 'pilot_id' => $colleague->id]);

        $response = $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('flightlist'))
            ->assertOk()
            ->assertSee('2:15')
            ->assertViewHas('totalFlights', 4)
            ->assertViewHas('statusCounts', ['pending' => 1, 'accepted' => 2, 'rejected' => 1]);
        $this->assertCount(4, $response->viewData('flights'));

        $flights = $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('flightlist', ['status' => 'accepted']))
            ->assertOk()
            ->assertViewHas('status', 'accepted')
            ->viewData('flights');

        $this->assertSame([2], $flights->pluck('status_id')->unique()->values()->all());
        $this->assertCount(2, $flights);
    }

    public function test_unknown_status_shows_all_flights(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        Flight::factory()->rejected()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('flightlist', ['status' => 'bogus']))
            ->assertOk()
            ->assertViewHas('status', null)
            ->assertViewHas('flights', fn ($flights) => $flights->count() === 2);
    }

    public function test_empty_filter_offers_to_show_all_flights(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        Flight::factory()->accepted()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('flightlist', ['status' => 'rejected']))
            ->assertOk()
            ->assertSee('You have no rejected flights')
            ->assertSee('Show all flights');
    }
}
