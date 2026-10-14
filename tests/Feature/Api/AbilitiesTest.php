<?php

namespace Tests\Feature\Api;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\Setting;
use App\Models\User;
use App\Support\Abilities;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * The `abilities` / `membership` the API reports to role-aware clients
 * (App\Support\Abilities). Every flag is pinned to the real allow/deny of the
 * endpoint or web page it unlocks, so the hints cannot drift from enforcement.
 */
class AbilitiesTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_user_reports_membership_and_abilities_per_role(): void
    {
        $airline = Airline::factory()->create();
        $expected = [
            'pilot' => [$this->memberOf($airline, 'Pilot'), false, ['fileFlights']],
            'dispatcher' => [$this->memberOf($airline, 'Dispatcher'), false, ['fileFlights', 'reviewFlights']],
            'manager' => [$this->ownerOf($airline), true, Abilities::AIRLINE],
        ];

        foreach ($expected as $role => [$user, $isOwner, $granted]) {
            Sanctum::actingAs($user);

            $this->getJson(route('api.v1.user'))
                ->assertOk()
                ->assertJsonPath('data.abilities', ['foundAirline' => false, 'administerInstance' => false])
                ->assertJsonPath('data.airlines.0.membership', [
                    'role' => $role,
                    'isOwner' => $isOwner,
                    'abilities' => $this->abilities($granted),
                ]);
        }
    }

    public function test_every_ability_matches_the_endpoint_it_unlocks(): void
    {
        $superAdminAsPilot = fn (Airline $airline) => tap($this->memberOf($airline, 'Pilot'))->assignRole('Super-Admin');

        $users = [
            'pilot' => fn (Airline $airline) => $this->memberOf($airline, 'Pilot'),
            'dispatcher' => fn (Airline $airline) => $this->memberOf($airline, 'Dispatcher'),
            'manager' => fn (Airline $airline) => $this->memberOf($airline, 'Manager'),
            'owner' => fn (Airline $airline) => $this->ownerOf($airline),
            'super-admin' => $superAdminAsPilot,
        ];

        foreach ($users as $label => $makeUser) {
            $airline = Airline::factory()->create();
            $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
            $user = $makeUser($airline);
            Sanctum::actingAs($user);

            $abilities = $this->getJson(route('api.v1.airlines.show', $airline))->json('data.membership.abilities');
            $this->assertSame(Abilities::AIRLINE, array_keys($abilities), $label);

            // API: authorization runs before validation, so an empty body is a 403 or a 422.
            $api = [
                'fileFlights' => fn () => $this->postJson(route('api.v1.airlines.flights.store', $airline)),
                'reviewFlights' => fn () => $this->getJson(route('api.v1.airlines.flights.review', $airline)),
                'addAircraft' => fn () => $this->postJson(route('api.v1.airlines.aircraft.store', $airline)),
            ];
            foreach ($api as $ability => $call) {
                $this->assertSame($abilities[$ability], $call()->status() !== 403, "{$label}: {$ability}");
            }

            // Web-only abilities: the page behind each one.
            $web = [
                'editAircraft' => route('editaircraft', $aircraft),
                'manageMembers' => route('members.index'),
                'manageInviteCodes' => route('invitecodes.index'),
                'manageNotams' => route('notams.index'),
                'editSettings' => route('airline.settings'),
            ];
            foreach ($web as $ability => $url) {
                $status = $this->actingAs($user)->withSession(['activeairline' => $airline])->get($url)->status();
                $this->assertSame($abilities[$ability], $status === 200, "{$label}: {$ability} ({$status})");
            }
        }
    }

    public function test_super_admin_reports_effective_abilities(): void
    {
        $airline = Airline::factory()->create();
        $admin = $this->memberOf($airline, 'Pilot');
        $admin->assignRole('Super-Admin');
        Sanctum::actingAs($admin);

        $this->getJson(route('api.v1.user'))
            ->assertJsonPath('data.abilities', ['foundAirline' => true, 'administerInstance' => true])
            ->assertJsonPath('data.airlines.0.membership.role', 'pilot')
            ->assertJsonPath('data.airlines.0.membership.abilities', $this->abilities(Abilities::AIRLINE));
    }

    public function test_suspended_airline_reports_no_abilities_and_refuses_writes(): void
    {
        $airline = Airline::factory()->create();
        $airline->forceFill(['suspended_at' => now(), 'suspension_reason' => 'Audit'])->save();
        $owner = $this->ownerOf($airline);
        $admin = tap($this->memberOf($airline, 'Pilot'))->assignRole('Super-Admin');

        foreach ([$owner, $admin] as $user) {
            Sanctum::actingAs($user);

            $this->getJson(route('api.v1.airlines.show', $airline))
                ->assertJsonPath('data.membership.abilities', $this->abilities([]));

            $this->postJson(route('api.v1.airlines.flights.store', $airline))->assertForbidden();
            $this->postJson(route('api.v1.airlines.aircraft.store', $airline))->assertForbidden();
        }
    }

    public function test_found_airline_ability_matches_the_endpoint(): void
    {
        Sanctum::actingAs(User::factory()->create());

        foreach (['0' => false, '1' => true] as $setting => $allowed) {
            Setting::set('allow_user_airline_creation', $setting);

            $this->getJson(route('api.v1.user'))->assertJsonPath('data.abilities.foundAirline', $allowed);
            $this->assertSame($allowed, $this->postJson(route('api.v1.airlines.store'))->status() !== 403);
        }
    }

    public function test_administer_instance_ability_matches_the_admin_area(): void
    {
        $user = User::factory()->create();
        $admin = tap(User::factory()->create())->assignRole('Super-Admin');

        foreach ([[$user, false], [$admin, true]] as [$account, $allowed]) {
            Sanctum::actingAs($account);
            $this->getJson(route('api.v1.user'))->assertJsonPath('data.abilities.administerInstance', $allowed);

            $this->assertSame($allowed, $this->actingAs($account)->get(route('admin.dashboard'))->status() === 200);
        }
    }

    public function test_founding_via_the_api_makes_the_founder_owner_and_manager(): void
    {
        Setting::set('allow_user_airline_creation', '1');
        $founder = User::factory()->create();
        Sanctum::actingAs($founder);

        $response = $this->postJson(route('api.v1.airlines.store'), [
            'name' => 'Commander Air', 'prefix' => 'CA', 'icao_callsign' => 'CMA', 'atc_callsign' => 'COMMANDER',
            'hub' => 'eddf', 'country' => 'de',
        ])->assertCreated()
            ->assertJsonPath('data.membership.role', 'manager')
            ->assertJsonPath('data.membership.isOwner', true)
            ->assertJsonPath('data.membership.abilities', $this->abilities(Abilities::AIRLINE));

        $airline = Airline::findOrFail($response->json('data.id'));
        $this->assertTrue($founder->isOwnerOf($airline));
        $this->assertSame('Manager', $founder->airlineRole($airline));
        $this->assertSame(['EDDF', 'DE', true], [$airline->hub, $airline->country, $airline->require_pirep_review]);
    }

    public function test_global_spatie_manager_role_grants_no_airline_rights(): void
    {
        $airline = Airline::factory()->create();
        $pilot = tap($this->memberOf($airline, 'Pilot'))->assignRole('Manager');
        Sanctum::actingAs($pilot);

        $this->postJson(route('api.v1.airlines.aircraft.store', $airline))->assertForbidden();
        $this->getJson(route('api.v1.airlines.flights.review', $airline))->assertForbidden();
    }

    public function test_membership_is_null_for_non_members_and_hidden_from_other_payloads(): void
    {
        $airline = Airline::factory()->create();
        $other = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        Sanctum::actingAs($pilot);

        $this->getJson(route('api.v1.airlines.show', $other))->assertJsonPath('data.membership', null);

        $this->getJson(route('api.v1.airlines.index', ['sort' => 'created_at']))
            ->assertJsonPath('data.0.membership.role', 'pilot')
            ->assertJsonPath('data.1.membership', null);

        // Payloads nesting the airline or the user (here: flights) carry neither.
        Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);
        $this->getJson(route('api.v1.airlines.flights.index', $airline))
            ->assertJsonMissingPath('data.0.airline.membership')
            ->assertJsonMissingPath('data.0.pilot.abilities');
    }

    public function test_reporting_abilities_does_not_query_per_airline(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $queries = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->getJson(route('api.v1.user'))->assertOk();

            return count(DB::getQueryLog());
        };

        $user->airlines()->attach(Airline::factory()->create()->id, ['role' => 'Manager']);
        $queries(); // Warm up: roles and permissions are loaded once per user.
        $one = $queries();

        foreach (Airline::factory()->count(4)->create() as $airline) {
            $user->airlines()->attach($airline->id, ['role' => 'Dispatcher']);
        }

        $this->assertSame($one, $queries());
    }

    /**
     * Every per-airline ability key, true only for the granted ones.
     *
     * @param  list<string>  $granted
     * @return array<string, bool>
     */
    private function abilities(array $granted): array
    {
        return collect(Abilities::AIRLINE)->mapWithKeys(fn ($ability) => [$ability => in_array($ability, $granted, true)])->all();
    }
}
