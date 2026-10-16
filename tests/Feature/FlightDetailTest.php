<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * The PIREP detail page: the record must show true facts (network, status
 * keyed on status_id) and explain the pending / rejected states.
 */
class FlightDetailTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_shows_the_filed_network_and_status_independent_of_status_names(): void
    {
        // Status names differ between installs (e.g. "New" for pending)
        DB::table('flight_statuses')->where('id', 1)->update(['name' => 'New']);

        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $flight = Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewflight', $flight))
            ->assertOk()
            ->assertSee('VATSIM')
            ->assertDontSee('Offline')
            ->assertSee('Pending')
            ->assertSee('Awaiting review')
            ->assertSee('href="'.route('flightlist').'"', false);
    }

    public function test_rejected_flight_without_reason_still_explains_the_rejection(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $flight = Flight::factory()->rejected()->create([
            'airline_id' => $airline->id, 'pilot_id' => $pilot->id, 'rejection_remarks' => null,
        ]);

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewflight', $flight))
            ->assertOk()
            ->assertSee('This PIREP was rejected')
            ->assertSee('No reason was given');
    }
}
