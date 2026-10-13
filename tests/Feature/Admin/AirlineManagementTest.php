<?php

namespace Tests\Feature\Admin;

use App\Models\Aircraft;
use App\Models\AircraftImage;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\InviteCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

class AirlineManagementTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super-Admin');
    }

    private function ownerOf(Airline $airline): User
    {
        $owner = $this->memberOf($airline, 'Manager');
        $airline->forceFill(['owner_user_id' => $owner->id])->save();

        return $owner;
    }

    private function suspend(Airline $airline): void
    {
        $airline->forceFill(['suspended_at' => now(), 'suspension_reason' => 'Audit'])->save();
    }

    private function profile(Airline $airline, array $overrides = []): array
    {
        return $overrides + [
            'name'                 => $airline->name,
            'prefix'               => $airline->prefix,
            'icao_callsign'        => $airline->icao_callsign,
            'atc_callsign'         => 'SPEEDBIRD', // the factory's random word may break min/max
            'hub'                  => 'EDDF',
            'country'              => $airline->country,
            'description'          => $airline->description,
            'website'              => null,
            'founded_at'           => '2020-01-01',
            'unit_is_lbs'          => '0',
            'require_pirep_review' => $airline->require_pirep_review ? '1' : '0',
            'location_continuity'  => $airline->location_continuity ? '1' : '0',
        ];
    }

    private function pirep(Aircraft $aircraft): array
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

    public function test_non_admins_are_forbidden(): void
    {
        $airline = Airline::factory()->create();
        $manager = $this->ownerOf($airline);

        $this->actingAs($manager)->get(route('admin.airlines.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.airlines.show', $airline))->assertForbidden();
        $this->actingAs($manager)->post(route('admin.airlines.suspend', $airline))->assertForbidden();

        $this->assertFalse($airline->fresh()->isSuspended());
    }

    public function test_index_searches_and_filters(): void
    {
        $alpha = Airline::factory()->create(['name' => 'Alpha Air', 'icao_callsign' => 'ALP']);
        $bravo = Airline::factory()->create(['name' => 'Bravo Wings', 'icao_callsign' => 'BRV']);
        $this->suspend($bravo);

        $this->actingAs($this->admin)
            ->get(route('admin.airlines.index', ['q' => 'ALP']))
            ->assertOk()
            ->assertSee('Alpha Air')
            ->assertDontSee('Bravo Wings');

        $this->actingAs($this->admin)
            ->get(route('admin.airlines.index', ['status' => 'suspended']))
            ->assertSee('Bravo Wings')
            ->assertDontSee('Alpha Air');

        // Neither factory airline has an owner
        $owned = Airline::factory()->create(['name' => 'Charlie Jet']);
        $this->ownerOf($owned);

        $this->actingAs($this->admin)
            ->get(route('admin.airlines.index', ['status' => 'orphaned']))
            ->assertSee('Alpha Air')
            ->assertDontSee('Charlie Jet');
    }

    public function test_show_renders(): void
    {
        $airline = Airline::factory()->create(['name' => 'Alpha Air']);
        $owner = $this->ownerOf($airline);
        Flight::factory()->create(['airline_id' => $airline->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.airlines.show', $airline))
            ->assertOk()
            ->assertSee('Alpha Air')
            ->assertSee($owner->email);

        $this->actingAs($this->admin)->get(route('admin.airlines.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->assertSee(route('admin.airlines.show', $airline));
        $this->actingAs($this->admin)->get(route('admin.activity.index', ['airline' => $airline->id]))->assertOk();
    }

    public function test_admin_can_update_profile_and_settings(): void
    {
        $airline = Airline::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.airlines.update', $airline), $this->profile($airline, [
                'name' => 'Renamed Air',
                'icao_callsign' => 'rnm',
                'location_continuity' => '1',
            ]))
            ->assertRedirect(route('admin.airlines.show', $airline));

        $airline->refresh();
        $this->assertSame('Renamed Air', $airline->name);
        $this->assertSame('RNM', $airline->icao_callsign);
        $this->assertTrue($airline->location_continuity);
        $this->assertTrue(Activity::where('event', 'airline_updated_by_admin')->exists());
    }

    public function test_review_cannot_be_disabled_while_flights_are_pending(): void
    {
        $airline = Airline::factory()->create(['require_pirep_review' => true]);
        Flight::factory()->create(['airline_id' => $airline->id, 'status_id' => 1]);

        $this->actingAs($this->admin)
            ->put(route('admin.airlines.update', $airline), $this->profile($airline, ['require_pirep_review' => '0']))
            ->assertSessionHasErrors('require_pirep_review', null, 'profile');

        $this->assertTrue($airline->fresh()->require_pirep_review);
    }

    public function test_admin_can_create_an_airline_for_a_user(): void
    {
        $owner = User::factory()->create();
        $template = Airline::factory()->make(['icao_callsign' => 'NEW']);

        $this->actingAs($this->admin)
            ->post(route('admin.airlines.store'), $this->profile($template, [
                'name' => 'New Air',
                'require_pirep_review' => '1',
                'owner_email' => $owner->email,
            ]))
            ->assertRedirect();

        $airline = Airline::where('name', 'New Air')->firstOrFail();
        $this->assertTrue($airline->isOwnedBy($owner));
        $this->assertTrue($owner->hasAirlineRole($airline, 'Manager'));
        $this->assertFalse($airline->isSuspended());
    }

    public function test_suspension_freezes_web_writes_but_keeps_reads(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $manager = $this->ownerOf($airline);

        $this->actingAs($this->admin)
            ->post(route('admin.airlines.suspend', $airline), ['reason' => 'Audit'])
            ->assertRedirect();
        $this->assertTrue($airline->fresh()->isSuspended());

        $session = ['activeairline' => $airline];

        $this->actingAs($manager)->withSession($session)
            ->post(route('flightadd'), $this->pirep($aircraft))
            ->assertSessionHas('error');
        $this->actingAs($manager)->withSession($session)
            ->post(route('invitecodes.generate'), ['role' => 'Pilot']);
        $this->actingAs($manager)->withSession($session)
            ->put(route('airline.settings.update'), ['location_continuity' => '1', 'require_pirep_review' => '1']);

        $this->assertDatabaseCount('flights', 0);
        $this->assertDatabaseCount('invite_codes', 0);
        $this->assertFalse($airline->fresh()->location_continuity);

        // Reads still work, with the read-only banner
        $this->actingAs($manager)->withSession($session)
            ->get(route('flightlist'))
            ->assertOk()
            ->assertSee('Airline suspended.');

        // Reactivation lifts the freeze
        $this->actingAs($this->admin)->delete(route('admin.airlines.reactivate', $airline));

        $this->actingAs($manager)->withSession($session)
            ->post(route('flightadd'), $this->pirep($aircraft));
        $this->assertDatabaseCount('flights', 1);
    }

    public function test_suspension_freezes_api_writes(): void
    {
        $airline = Airline::factory()->create();
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        $manager = $this->ownerOf($airline);
        $flight = Flight::factory()->create(['airline_id' => $airline->id, 'status_id' => 1]);
        $this->suspend($airline);

        Sanctum::actingAs($manager);

        $this->postJson(route('api.v1.airlines.flights.store', $airline), $this->pirep($aircraft))->assertForbidden();
        $this->postJson(route('api.v1.flights.accept', $flight))->assertForbidden();
        $this->getJson(route('api.v1.airlines.flights.index', $airline))->assertOk();

        $this->assertSame(1, $flight->fresh()->status_id);
    }

    public function test_suspended_airline_is_hidden_and_invites_cannot_be_redeemed(): void
    {
        $airline = Airline::factory()->create(['name' => 'Frozen Air']);
        $creator = $this->ownerOf($airline);
        $code = InviteCode::factory()->create(['airline_id' => $airline->id, 'created_by' => $creator->id]);
        $this->suspend($airline);

        $this->get(route('home'))->assertDontSee('Frozen Air');

        $joiner = User::factory()->create();
        $this->actingAs($joiner)
            ->post(route('portal.redeem'), ['code' => $code->code])
            ->assertSessionHasErrors('code');

        $this->assertFalse($airline->isMember($joiner));
    }

    public function test_member_management(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $pilot = $this->memberOf($airline, 'Pilot');
        $newcomer = User::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.airlines.members.store', $airline), ['email' => $newcomer->email, 'role' => 'Dispatcher']);
        $this->assertTrue($newcomer->hasAirlineRole($airline, 'Dispatcher'));

        $this->actingAs($this->admin)
            ->put(route('admin.airlines.members.update', [$airline, $pilot]), ['role' => 'Manager']);
        $this->assertTrue($pilot->hasAirlineRole($airline, 'Manager'));

        $this->actingAs($this->admin)
            ->delete(route('admin.airlines.members.destroy', [$airline, $newcomer]));
        $this->assertFalse($airline->isMember($newcomer));

        // The owner can be neither demoted nor removed
        $this->actingAs($this->admin)
            ->put(route('admin.airlines.members.update', [$airline, $owner]), ['role' => 'Pilot'])
            ->assertSessionHasErrors('action');
        $this->actingAs($this->admin)
            ->delete(route('admin.airlines.members.destroy', [$airline, $owner]))
            ->assertSessionHasErrors('action');
        $this->assertTrue($owner->hasAirlineRole($airline, 'Manager'));
    }

    public function test_last_manager_cannot_be_demoted_or_removed(): void
    {
        $airline = Airline::factory()->create(); // no owner
        $manager = $this->memberOf($airline, 'Manager');

        $this->actingAs($this->admin)
            ->put(route('admin.airlines.members.update', [$airline, $manager]), ['role' => 'Pilot'])
            ->assertSessionHasErrors('action');
        $this->actingAs($this->admin)
            ->delete(route('admin.airlines.members.destroy', [$airline, $manager]))
            ->assertSessionHasErrors('action');

        $this->assertTrue($manager->hasAirlineRole($airline, 'Manager'));
    }

    public function test_ownership_can_be_assigned_to_an_orphaned_airline(): void
    {
        $airline = Airline::factory()->create(); // no owner
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($this->admin)
            ->put(route('admin.airlines.owner', $airline), ['user_id' => $pilot->id])
            ->assertRedirect(route('admin.airlines.show', $airline));

        $this->assertTrue($airline->fresh()->isOwnedBy($pilot));
        $this->assertTrue($pilot->hasAirlineRole($airline, 'Manager'));
        $this->assertTrue(Activity::where('event', 'airline_ownership_transferred_by_admin')->exists());
    }

    public function test_ownership_cannot_go_to_a_non_member(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $outsider = User::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.airlines.owner', $airline), ['user_id' => $outsider->id])
            ->assertSessionHasErrors('action');

        $this->assertTrue($airline->fresh()->isOwnedBy($owner));
    }

    public function test_delete_requires_matching_icao(): void
    {
        $airline = Airline::factory()->create(['icao_callsign' => 'DEL']);

        $this->actingAs($this->admin)
            ->delete(route('admin.airlines.destroy', $airline), ['confirm_icao' => 'XXX'])
            ->assertSessionHasErrors('confirm_icao', null, 'deleteAirline');

        $this->assertModelExists($airline);
    }

    public function test_delete_removes_the_airline_and_everything_it_owns(): void
    {
        Storage::fake('local');

        $airline = Airline::factory()->create(['icao_callsign' => 'DEL']);
        $owner = $this->ownerOf($airline);
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        Flight::factory()->create(['airline_id' => $airline->id, 'aircraft_id' => $aircraft->id]);
        $image = AircraftImage::factory()->create(['aircraft_id' => $aircraft->id]);
        Storage::disk('local')->put($image->path, 'img');
        InviteCode::factory()->create(['airline_id' => $airline->id, 'created_by' => $owner->id]);

        $other = Airline::factory()->create();
        Flight::factory()->create(['airline_id' => $other->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.airlines.destroy', $airline), ['confirm_icao' => 'DEL'])
            ->assertRedirect(route('admin.airlines.index'));

        $this->assertModelMissing($airline);
        $this->assertSame(0, Flight::where('airline_id', $airline->id)->count());
        $this->assertSame(0, Aircraft::where('used_by', $airline->id)->count());
        $this->assertDatabaseCount('aircraft_images', 0);
        $this->assertDatabaseCount('invite_codes', 0);
        $this->assertDatabaseMissing('airline_memberships', ['airline_id' => $airline->id]);
        Storage::disk('local')->assertMissing($image->path);
        $this->assertModelExists($owner);
        $this->assertSame(1, Flight::where('airline_id', $other->id)->count());
        $this->assertTrue(Activity::where('event', 'airline_deleted_by_admin')->exists());

        // A member who still has it as active airline is sent back to the portal
        $this->actingAs($owner)
            ->withSession(['activeairline' => $airline])
            ->get(route('flightlist'))
            ->assertRedirect(route('portal'));
    }

    public function test_removed_member_loses_the_active_airline_session(): void
    {
        $airline = Airline::factory()->create();
        $this->ownerOf($airline);
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($this->admin)->delete(route('admin.airlines.members.destroy', [$airline, $pilot]));

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('flightlist'))
            ->assertRedirect(route('portal'));
    }
}
