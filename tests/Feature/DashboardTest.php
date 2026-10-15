<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * The dashboard shows the pilot's own record: their latest PIREP in any state
 * as "Last Duty", and block time in hours and minutes.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_last_duty_is_the_pilots_own_flight_not_the_airlines_latest(): void
    {
        $airline = Airline::factory()->create(['icao_callsign' => 'TST']);
        $pilot = $this->memberOf($airline, 'Pilot');
        $colleague = $this->memberOf($airline, 'Pilot');

        Flight::factory()->accepted()->create([
            'airline_id' => $airline->id, 'pilot_id' => $pilot->id, 'flightnumber' => 100,
            'created_at' => now()->subDay(),
        ]);
        Flight::factory()->accepted()->create([
            'airline_id' => $airline->id, 'pilot_id' => $colleague->id, 'flightnumber' => 999,
        ]);

        $view = $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('dashboard'))
            ->assertOk()
            ->viewData('my_flights');

        $this->assertSame([$pilot->id], $view->pluck('pilot_id')->unique()->values()->all());
        $this->assertSame(100, $view->first()->flightnumber);
    }

    public function test_pending_and_rejected_pireps_are_listed_with_their_status(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');

        Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        Flight::factory()->rejected()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pending')
            ->assertSee('Rejected');
    }

    public function test_block_time_keeps_minutes_and_counts_only_accepted_flights(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');

        // 90 min accepted (factory default) + 45 min accepted; pending is ignored
        Flight::factory()->accepted()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        Flight::factory()->accepted()->create([
            'airline_id' => $airline->id, 'pilot_id' => $pilot->id,
            'blockoff' => '2026-07-12 10:00:00', 'blockon' => '2026-07-12 10:45:00',
        ]);
        Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        $this->assertSame(135, $pilot->logged_minutes($airline));
        $this->assertSame(2, $pilot->logged_hours($airline));

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('2:15');
    }

    public function test_new_pilot_is_offered_to_file_a_first_pirep(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('You have not filed a PIREP')
            ->assertSee(route('flightadd'));
    }
}
