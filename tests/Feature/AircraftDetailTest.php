<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

class AircraftDetailTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_block_time_sums_accepted_flights_to_the_minute(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);

        // 90 + 45 minutes accepted; the pending flight does not count.
        Flight::factory()->accepted()->create(['airline_id' => $airline->id, 'aircraft_id' => $aircraft->id]);
        Flight::factory()->accepted()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'blockoff' => '2026-07-12 10:00:00',
            'blockon' => '2026-07-12 10:45:00',
        ]);
        Flight::factory()->create(['airline_id' => $airline->id, 'aircraft_id' => $aircraft->id]);

        $this->assertSame(135, $aircraft->total_block_minutes);
        $this->assertSame('2:15 h', $aircraft->total_block_time);
        $this->assertSame(2.3, $aircraft->total_flights_hours);
    }

    public function test_distance_flown_sums_accepted_routes_in_constant_queries(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        Airport::factory()->icao('XXAA', 0.0, 0.0)->create();
        Airport::factory()->icao('XXBB', 0.0, 1.0)->create(); // 1 degree of longitude on the equator: 60 NM

        Flight::factory()->accepted()->count(5)->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'departure_icao' => 'XXAA',
            'arrival_icao' => 'XXBB',
        ]);
        Flight::factory()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'departure_icao' => 'XXAA',
            'arrival_icao' => 'XXBB',
        ]);

        DB::enableQueryLog();
        $distance = $aircraft->total_distance_flown;
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertEquals(300, $distance);
        $this->assertSame(3, $queries);
    }

    public function test_serializing_an_aircraft_runs_no_aggregate_queries(): void
    {
        $aircraft = Aircraft::factory()->create();

        DB::enableQueryLog();
        $data = $aircraft->toArray();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(0, $queries);
        $this->assertArrayHasKey('in_service_since', $data);
        $this->assertArrayNotHasKey('total_distance_flown', $data);
    }

    public function test_detail_page_shows_block_time_and_orders_recent_flights_by_block_on(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $manager = $this->memberOf($airline, 'Manager');

        // Filed first, but flown last: it must lead the list.
        Flight::factory()->accepted()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'flightnumber' => 1111,
            'blockoff' => '2026-07-20 10:00:00',
            'blockon' => '2026-07-20 11:30:00',
        ]);
        Flight::factory()->accepted()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'flightnumber' => 2222,
        ]);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewaircraft', $aircraft->id))
            ->assertOk()
            ->assertSee('3:00 h')
            ->assertSeeInOrder(['1111', '2222']);
    }

    public function test_spec_values_and_missing_data_render_consistently(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create([
            'used_by' => $airline->id,
            'mtow' => 79000,
            'mlw' => null,
            'msn' => null,
        ]);
        $manager = $this->memberOf($airline, 'Manager');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewaircraft', $aircraft->id))
            ->assertOk()
            ->assertSee('79,000 kg')
            ->assertSee('Not specified')
            ->assertDontSee('@else')
            ->assertDontSee('N/A');
    }

    public function test_retired_aircraft_reads_last_seen_at(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $aircraft->forceFill(['status' => Aircraft::STATUS_RETIRED, 'retired_at' => now()])->save();
        $manager = $this->memberOf($airline, 'Manager');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewaircraft', $aircraft->id))
            ->assertOk()
            ->assertSee('Last seen at')
            ->assertDontSee('On the ground at');
    }

    public function test_suspended_airline_offers_no_upload_tab(): void
    {
        $airline = Airline::factory()->create();
        $airline->forceFill(['suspended_at' => now(), 'suspension_reason' => 'Test'])->save();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewaircraft', $aircraft->id))
            ->assertOk()
            ->assertDontSee('upload-tab-btn-'.$aircraft->id);
    }
}
