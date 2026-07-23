<?php

namespace Tests\Concerns;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Shared setup for feature tests: seeds the reference data that RefreshDatabase
 * does not (flight statuses, online networks, Spatie roles, a few airports) and
 * exposes small builders for the domain's airline-membership model.
 */
trait SeedsDomain
{
    /**
     * A handful of real airports with coordinates, keyed by ICAO. Enough for
     * location-continuity and Haversine-distance assertions without loading the
     * full airports.sql dump.
     */
    protected array $airportCoords = [
        'EDDF' => [50.0333, 8.5706],   // Frankfurt
        'EDDM' => [48.3538, 11.7861],  // Munich
        'EGLL' => [51.4700, -0.4543],  // London Heathrow
        'KJFK' => [40.6413, -73.7781], // New York JFK
        'KLAX' => [33.9416, -118.4085], // Los Angeles
    ];

    protected function seedReferenceData(): void
    {
        $now = now();

        // Hardcoded flight statuses (see Flight Status IDs in the docs).
        DB::table('flight_statuses')->insert([
            ['id' => 1, 'name' => 'Pending', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Accepted', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Rejected', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // A single online network is enough for the FK on flights.online_network_id.
        DB::table('online_networks')->insert([
            'id' => 1, 'networkname' => 'VATSIM', 'created_at' => $now, 'updated_at' => $now,
        ]);

        // Global Spatie roles used by Gate::before and hasRole() checks.
        foreach (['Pilot', 'Manager', 'Super-Admin'] as $role) {
            Role::findOrCreate($role, 'web');
        }
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Reference airports with known coordinates.
        foreach ($this->airportCoords as $icao => [$lat, $lon]) {
            Airport::factory()->icao($icao, $lat, $lon)->create();
        }
    }

    /**
     * Create a user and attach them to the airline with the given per-airline
     * role (Pilot | Dispatcher | Manager).
     */
    protected function memberOf(Airline $airline, string $role = 'Pilot'): User
    {
        $user = User::factory()->create();
        $user->airlines()->attach($airline->id, ['role' => $role]);

        return $user;
    }

    /**
     * Create a user, attach them to the airline as a Manager, and set them
     * as the owner of the airline.
     */
    protected function ownerOf(Airline $airline): User
    {
        $user = User::factory()->create();
        $user->airlines()->attach($airline->id, ['role' => 'Manager']);
        $airline->owner_user_id = $user->id;
        $airline->save();

        return $user;
    }
}
