<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    private function roleOf($user, Airline $airline): ?string
    {
        return $user->fresh()->airlines()->find($airline->id)?->pivot->role;
    }

    public function test_founding_sets_owner_and_manager_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Super-Admin');

        $this->actingAs($user)
            ->post(route('airline.found.store'), [
                'airline_name'     => 'Test Airline',
                'airline_prefix'   => 'TA',
                'airline_icao'     => 'TAL',
                'airline_callsign' => 'TESTAIR',
                'airline_hub'      => 'EDDF',
                'airline_country'  => 'DE',
                'airline_desc'     => 'A test airline',
                'airline_website'  => 'https://example.com',
                'airline_founded'  => '2026-07-23',
                'unit_is_lbs'      => 0,
            ])
            ->assertRedirect();

        $airline = Airline::where('icao_callsign', 'TAL')->first();
        $this->assertNotNull($airline);
        $this->assertSame($user->id, $airline->owner_user_id);
        $this->assertTrue($user->isManagerOf($airline));
        $this->assertSame('Manager', $this->roleOf($user, $airline));
    }

    public function test_owner_can_promote_pilot_to_manager(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($owner)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $pilot), ['role' => 'Manager'])
            ->assertRedirect();

        $this->assertSame('Manager', $this->roleOf($pilot, $airline));
    }

    public function test_owner_can_demote_manager_to_pilot(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $manager = $this->memberOf($airline, 'Manager');

        $this->actingAs($owner)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $manager), ['role' => 'Pilot'])
            ->assertRedirect();

        $this->assertSame('Pilot', $this->roleOf($manager, $airline));
    }

    public function test_non_owner_manager_can_change_pilot_to_dispatcher(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $manager = $this->memberOf($airline, 'Manager');
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $pilot), ['role' => 'Dispatcher'])
            ->assertRedirect();

        $this->assertSame('Dispatcher', $this->roleOf($pilot, $airline));
    }

    public function test_non_owner_manager_cannot_promote_to_manager(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $manager = $this->memberOf($airline, 'Manager');
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $pilot), ['role' => 'Manager'])
            ->assertForbidden();

        $this->assertSame('Pilot', $this->roleOf($pilot, $airline));
    }

    public function test_non_owner_manager_cannot_demote_manager(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $manager = $this->memberOf($airline, 'Manager');
        $otherManager = $this->memberOf($airline, 'Manager');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $otherManager), ['role' => 'Pilot'])
            ->assertForbidden();

        $this->assertSame('Manager', $this->roleOf($otherManager, $airline));
    }

    public function test_non_owner_manager_cannot_remove_manager(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $manager = $this->memberOf($airline, 'Manager');
        $otherManager = $this->memberOf($airline, 'Manager');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->delete(route('members.destroy', $otherManager))
            ->assertForbidden();

        $this->assertTrue($airline->isMember($otherManager->fresh()));
    }

    public function test_nobody_can_update_or_remove_owner(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $manager = $this->memberOf($airline, 'Manager');

        // Manager trying to update owner
        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $owner), ['role' => 'Pilot'])
            ->assertForbidden();

        // Manager trying to remove owner
        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->delete(route('members.destroy', $owner))
            ->assertForbidden();

        // Owner trying to demote self
        $this->actingAs($owner)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $owner), ['role' => 'Pilot'])
            ->assertForbidden();

        // Owner trying to remove self
        $this->actingAs($owner)
            ->withSession(['activeairline' => $airline])
            ->delete(route('members.destroy', $owner))
            ->assertForbidden();

        $this->assertSame('Manager', $this->roleOf($owner, $airline));
        $this->assertTrue($airline->isMember($owner->fresh()));
    }

    public function test_owner_can_transfer_ownership(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $member = $this->memberOf($airline, 'Pilot');

        $this->actingAs($owner)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.transfer', $member))
            ->assertRedirect();

        $airline = $airline->fresh();
        $this->assertSame($member->id, $airline->owner_user_id);
        $this->assertSame('Manager', $this->roleOf($owner, $airline));
        $this->assertSame('Manager', $this->roleOf($member, $airline));
        $this->assertSame($airline->owner_user_id, session('activeairline')->owner_user_id);
    }

    public function test_non_owner_cannot_transfer_ownership(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $manager = $this->memberOf($airline, 'Manager');
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.transfer', $pilot))
            ->assertForbidden();

        $this->assertSame($owner->id, $airline->fresh()->owner_user_id);
    }

    public function test_owner_cannot_transfer_to_self(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);

        $this->actingAs($owner)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.transfer', $owner))
            ->assertStatus(422);

        $this->assertSame($owner->id, $airline->fresh()->owner_user_id);
    }

    public function test_non_manager_cannot_access_members(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('members.index'))
            ->assertForbidden();
    }

    public function test_manager_cannot_update_member_of_another_airline(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $manager = $this->memberOf($airline, 'Manager');

        $otherAirline = Airline::factory()->create();
        $outsider     = $this->memberOf($otherAirline, 'Pilot');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $outsider), ['role' => 'Manager'])
            ->assertForbidden();
    }

    public function test_manager_can_remove_pilot(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $manager = $this->memberOf($airline, 'Manager');
        $pilot = $this->memberOf($airline, 'Pilot');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->delete(route('members.destroy', $pilot))
            ->assertRedirect();

        $this->assertFalse($airline->isMember($pilot->fresh()));
    }
}
