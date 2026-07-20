<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * The Fleet Overview page plots the whole active-airline fleet on a map, one
 * marker per airport (aircraft grouped), excluding retired airframes.
 */
class FleetMapTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_fleet_map_groups_aircraft_by_airport_and_excludes_retired(): void
    {
        $airline = Airline::factory()->create();

        // Two airframes share Frankfurt, one sits at Heathrow, one retired at JFK.
        $eddfOne = Aircraft::factory()->at('EDDF')->create(['used_by' => $airline->id]);
        $eddfTwo = Aircraft::factory()->at('EDDF')->create(['used_by' => $airline->id]);
        $egll = Aircraft::factory()->at('EGLL')->create(['used_by' => $airline->id]);
        $retired = Aircraft::factory()->retired()->at('KJFK')->create(['used_by' => $airline->id]);

        $pilot = $this->memberOf($airline, 'Pilot');

        $response = $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('fleetmanager'));

        $response->assertOk();
        $response->assertSee('fleetMap');

        // One marker per airport: Frankfurt + Heathrow = 2 (not 3 aircraft).
        $this->assertSame(2, substr_count($response->getContent(), 'L.marker(['));

        // Both Frankfurt airframes are listed in a popup; the retired one is gone.
        $response->assertSee($eddfOne->registration);
        $response->assertSee($eddfTwo->registration);
        $response->assertSee($egll->registration);
        $response->assertDontSee($retired->registration);
    }

    public function test_fleet_map_shows_empty_state_without_a_leaflet_map(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');

        $response = $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('fleetmanager'));

        $response->assertOk();
        $response->assertSee('No aircraft with a known location yet.');
        $this->assertStringNotContainsString('L.marker([', $response->getContent());
    }
}
