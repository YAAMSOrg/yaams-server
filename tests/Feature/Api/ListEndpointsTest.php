<?php

namespace Tests\Feature\Api;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * API v1 list endpoints - shared pagination / sort / filter contract
 * (App\Http\Requests\Api\V1\ListRequest) and the per-endpoint filters.
 */
class ListEndpointsTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_lists_are_paginated_with_links_and_meta(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        Flight::factory()->count(3)->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        Sanctum::actingAs($pilot);

        $this->getJson(route('api.v1.airlines.flights.index', [$airline, 'per_page' => 2, 'status' => 'pending']))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonMissingPath('meta.links')
            ->assertJsonMissingPath('meta.count')
            // Pagination links keep the filters.
            ->assertJsonPath('links.next', fn (string $url) => str_contains($url, 'status=pending') && str_contains($url, 'page=2'));
    }

    public function test_per_page_defaults_and_bounds(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('api.v1.airlines.index'))->assertOk()->assertJsonPath('meta.per_page', 25);

        foreach (['0', '101', 'abc'] as $perPage) {
            $this->getJson(route('api.v1.airlines.index', ['per_page' => $perPage]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('per_page');
        }
    }

    public function test_unknown_sort_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('api.v1.airlines.index', ['sort' => 'password']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    public function test_airlines_can_be_searched_filtered_and_sorted(): void
    {
        $user = User::factory()->create();
        $alpha = Airline::factory()->create(['name' => 'Alpha Air']);
        $bravo = Airline::factory()->create(['name' => 'Bravo Wings']);
        $charlie = Airline::factory()->create(['name' => 'Charlie Air']);
        $charlie->forceFill(['suspended_at' => now()])->save();
        $user->airlines()->attach($bravo->id, ['role' => 'Pilot']);
        Sanctum::actingAs($user);

        $this->getJson(route('api.v1.airlines.index', ['sort' => '-name']))
            ->assertJsonPath('data.*.id', [$charlie->id, $bravo->id, $alpha->id]);

        $this->getJson(route('api.v1.airlines.index', ['search' => 'air', 'suspended' => 'false']))
            ->assertJsonPath('data.*.id', [$alpha->id]);

        $this->getJson(route('api.v1.airlines.index', ['members_only' => 'true', 'sort' => '-created_at']))
            ->assertJsonPath('data.*.id', [$bravo->id]);
    }

    public function test_aircraft_can_be_filtered(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $inFrankfurt = Aircraft::factory()->at('EDDF')->create(['used_by' => $airline->id, 'registration' => 'D-AAAA']);
        Aircraft::factory()->at('EGLL')->create(['used_by' => $airline->id, 'registration' => 'G-BBBB']);
        Aircraft::factory()->at('EDDF')->create(['used_by' => $airline->id, 'registration' => 'D-CCCC', 'status' => Aircraft::STATUS_RETIRED]);
        Sanctum::actingAs($pilot);

        $this->getJson(route('api.v1.airlines.aircraft.index', [$airline, 'location' => 'eddf', 'status' => 'active']))
            ->assertOk()
            ->assertJsonPath('data.*.id', [$inFrankfurt->id]);

        $this->getJson(route('api.v1.airlines.aircraft.index', [$airline, 'search' => 'd-']))
            ->assertJsonPath('meta.total', 2);

        $this->getJson(route('api.v1.airlines.aircraft.index', [$airline, 'status' => 'scrapped']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_flights_can_be_filtered_by_route_aircraft_and_date(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $match = Flight::factory()->accepted()->create([
            'airline_id' => $airline->id, 'pilot_id' => $pilot->id,
            'departure_icao' => 'EDDF', 'arrival_icao' => 'EGLL', 'blockoff' => '2026-07-11 23:30:00', 'blockon' => '2026-07-12 01:00:00',
        ]);
        Flight::factory()->accepted()->create([
            'airline_id' => $airline->id, 'pilot_id' => $pilot->id,
            'departure_icao' => 'EDDF', 'arrival_icao' => 'EGLL', 'blockoff' => '2026-07-12 10:00:00', 'blockon' => '2026-07-12 11:30:00',
        ]);
        Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id, 'departure_icao' => 'EDDF']);
        Sanctum::actingAs($pilot);

        $this->getJson(route('api.v1.airlines.flights.index', [
            $airline, 'status' => 'accepted', 'departure' => 'eddf', 'arrival' => 'EGLL', 'from' => '2026-07-11', 'to' => '2026-07-11',
        ]))->assertOk()->assertJsonPath('data.*.id', [$match->id]);

        $this->getJson(route('api.v1.airlines.flights.index', [$airline, 'aircraft_id' => $match->aircraft_id]))
            ->assertJsonPath('data.*.id', [$match->id]);

        $this->getJson(route('api.v1.airlines.flights.index', [$airline, 'status' => 'accepted', 'sort' => 'block_off']))
            ->assertJsonPath('data.0.id', $match->id);

        // The deprecated status_id alias still works.
        $this->getJson(route('api.v1.airlines.flights.index', [$airline, 'status_id' => 1]))
            ->assertJsonPath('meta.total', 1);

        $this->getJson(route('api.v1.airlines.flights.index', [$airline, 'from' => '2026-07-12', 'to' => '2026-07-11']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('to');
    }

    public function test_flights_can_be_sorted_by_every_column(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $flight = fn (array $attributes) => Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id] + $attributes);

        // Each column orders the three flights differently from their IDs, so
        // a sort silently falling back to the tie-breaker would fail.
        $a = $flight([
            'flightnumber' => 100, 'callsign' => 'MM1', 'departure_icao' => 'KJFK', 'arrival_icao' => 'EDDM',
            'blockoff' => '2026-07-12 10:00:00', 'blockon' => '2026-07-12 11:00:00', // 1 h
            'burned_fuel' => 900, 'status_id' => 2, 'created_at' => now()->subDays(1),
        ]);
        $b = $flight([
            'flightnumber' => 9, 'callsign' => 'AB1', 'departure_icao' => 'EDDF', 'arrival_icao' => 'KLAX',
            'blockoff' => '2026-07-10 10:00:00', 'blockon' => '2026-07-12 12:00:00', // 50 h, spans days
            'burned_fuel' => 12000, 'status_id' => 3, 'created_at' => now()->subDays(3),
        ]);
        $c = $flight([
            'flightnumber' => 10, 'callsign' => 'ZZ1', 'departure_icao' => 'EGLL', 'arrival_icao' => 'EGLL',
            'blockoff' => '2026-07-11 10:00:00', 'blockon' => '2026-07-11 12:00:00', // 2 h
            'burned_fuel' => 5000, 'status_id' => 1, 'created_at' => now()->subDays(2),
        ]);
        Sanctum::actingAs($pilot);

        $ascending = [
            'flight_number' => [$b, $c, $a], // numeric: a string sort would give 10, 100, 9
            'callsign' => [$b, $a, $c],
            'departure' => [$b, $c, $a],
            'arrival' => [$a, $c, $b],
            'block_off' => [$b, $c, $a],
            'block_on' => [$c, $a, $b],
            'duration' => [$a, $c, $b],
            'burned_fuel' => [$a, $c, $b], // numeric: a string sort would give 12000, 5000, 900
            'status' => [$c, $a, $b], // workflow order: pending, accepted, rejected
            'created_at' => [$b, $c, $a],
        ];

        foreach ($ascending as $key => $flights) {
            $ids = array_map(fn (Flight $f) => $f->id, $flights);

            $this->getJson(route('api.v1.airlines.flights.index', [$airline, 'sort' => $key]))
                ->assertOk()
                ->assertJsonPath('data.*.id', $ids, "sort={$key}");

            $this->getJson(route('api.v1.airlines.flights.index', [$airline, 'sort' => '-'.$key]))
                ->assertOk()
                ->assertJsonPath('data.*.id', array_reverse($ids), "sort=-{$key}");
        }
    }

    public function test_flight_sorts_page_stably_on_ties(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        // Same fuel and the same 90 min duration, on different days.
        $ids = collect(range(1, 5))
            ->map(fn (int $day) => Flight::factory()->create([
                'airline_id' => $airline->id, 'pilot_id' => $pilot->id, 'burned_fuel' => 5000,
                'blockoff' => "2026-07-0{$day} 10:00:00", 'blockon' => "2026-07-0{$day} 11:30:00",
            ])->id)
            ->all();
        Sanctum::actingAs($pilot);

        foreach (['burned_fuel', 'duration', '-burned_fuel', '-duration'] as $sort) {
            $paged = collect(range(1, 3))
                ->flatMap(fn (int $page) => $this->getJson(route('api.v1.airlines.flights.index', [
                    $airline, 'sort' => $sort, 'per_page' => 2, 'page' => $page,
                ]))->assertOk()->json('data.*.id'))
                ->all();

            // Ties fall back to the ID in the sort direction: no row is skipped or repeated.
            $this->assertSame(str_starts_with($sort, '-') ? array_reverse($ids) : $ids, $paged, "sort={$sort}");
        }
    }

    public function test_unknown_flight_sort_is_rejected(): void
    {
        $airline = Airline::factory()->create();
        $dispatcher = $this->memberOf($airline, 'Dispatcher');
        Sanctum::actingAs($dispatcher);

        foreach (['route', '-pilot_id', 'flightnumber', '--duration'] as $sort) {
            $this->getJson(route('api.v1.airlines.flights.index', [$airline, 'sort' => $sort]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('sort');
        }

        // The review queue shares the flight sorts, except `status` (it only holds pending flights).
        $this->getJson(route('api.v1.airlines.flights.review', [$airline, 'sort' => '-duration']))->assertOk();
        $this->getJson(route('api.v1.airlines.flights.review', [$airline, 'sort' => 'status']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    public function test_flights_list_only_contains_the_pilots_own_flights(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $this->memberOf($airline, 'Pilot')->id]);
        Sanctum::actingAs($pilot);

        $this->getJson(route('api.v1.airlines.flights.index', $airline))->assertJsonPath('meta.total', 0);
    }

    public function test_review_queue_is_paginated_and_filterable_by_pilot(): void
    {
        $airline = Airline::factory()->create();
        $dispatcher = $this->memberOf($airline, 'Dispatcher');
        $pilot = $this->memberOf($airline, 'Pilot');
        $pending = Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        Flight::factory()->create(['airline_id' => $airline->id]);
        Flight::factory()->accepted()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        Sanctum::actingAs($dispatcher);

        $this->getJson(route('api.v1.airlines.flights.review', [$airline, 'pilot_id' => $pilot->id]))
            ->assertOk()
            ->assertJsonPath('data.*.id', [$pending->id])
            ->assertJsonPath('meta.total', 1);
    }

    public function test_authorization_is_checked_before_validation(): void
    {
        $airline = Airline::factory()->create();
        Sanctum::actingAs($this->memberOf($airline, 'Pilot'));

        $this->getJson(route('api.v1.airlines.flights.review', [$airline, 'per_page' => 0]))
            ->assertForbidden()
            ->assertJsonPath('message', 'You do not have permission to review flights for this airline.');

        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('api.v1.airlines.aircraft.index', [$airline, 'status' => 'bogus']))
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not a member of this airline.');
    }

    public function test_api_errors_are_json_without_an_accept_header(): void
    {
        $this->get(route('api.v1.airlines.index'))
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');

        Sanctum::actingAs(User::factory()->create());

        $this->get(route('api.v1.airlines.index', ['per_page' => 0]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }
}
