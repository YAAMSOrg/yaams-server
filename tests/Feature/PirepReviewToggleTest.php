<?php

namespace Tests\Feature;

use App\Events\FlightFiled;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * The per-airline require_pirep_review flag decides whether a filed PIREP waits
 * in the review queue (and notifies reviewers) or is auto-accepted.
 */
class PirepReviewToggleTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    private function payload(Aircraft $aircraft): array
    {
        return [
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

    public function test_filing_stays_pending_and_notifies_reviewers_when_review_is_on(): void
    {
        Event::fake([FlightFiled::class]);

        $airline = Airline::factory()->create(); // review on by default
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightadd'), $this->payload($aircraft));

        $this->assertSame(1, Flight::firstOrFail()->status_id);
        Event::assertDispatched(FlightFiled::class);
    }

    public function test_filing_is_auto_accepted_and_dispatches_no_event_when_review_is_off(): void
    {
        Event::fake([FlightFiled::class]);

        $airline = Airline::factory()->noReview()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightadd'), $this->payload($aircraft));

        $this->assertSame(2, Flight::firstOrFail()->status_id);
        Event::assertNotDispatched(FlightFiled::class);
    }
}
