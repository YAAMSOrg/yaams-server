<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Issue #101: lowercase ICAO codes made eager-loaded airport relations resolve
 * to null (Eloquent pairs keys case-sensitively), which 500'd the aircraft page.
 */
class IcaoCaseTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_web_filing_stores_icao_codes_uppercase(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightadd'), [
                'flightnumber' => 421,
                'departure_icao' => 'eddf',
                'arrival_icao' => 'egll',
                'aircraft_id' => $aircraft->id,
                'callsign' => '421',
                'crzalt' => 35000,
                'blockoff' => '2026-07-11 10:00:00',
                'blockon' => '2026-07-11 11:30:00',
                'burned_fuel' => 5000,
                'route' => 'DCT',
                'online_network_id' => 1,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('flights', ['departure_icao' => 'EDDF', 'arrival_icao' => 'EGLL']);
    }

    public function test_aircraft_page_renders_when_airport_relations_are_missing(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $pilot = $this->memberOf($airline, 'Pilot');

        // Legacy rows filed before the uppercase normalization. SQLite matches FKs
        // case-sensitively, so defer the check (the test transaction never commits).
        DB::statement("PRAGMA defer_foreign_keys = ON");
        Flight::factory()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'pilot_id' => $pilot->id,
            'status_id' => 2,
            'departure_icao' => 'eddf',
            'arrival_icao' => 'egll',
        ]);
        $aircraft->update(['current_loc' => 'eddf']);

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewaircraft', $aircraft->fresh()))
            ->assertOk()
            ->assertSee('eddf');
    }
}
