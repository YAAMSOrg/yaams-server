<?php

namespace Tests\Feature;

use App\Models\Airline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Airline Managers can change other members' per-airline roles and remove members,
 * with a guard that the airline is never left without a Manager.
 */
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

    public function test_manager_can_change_a_members_role(): void
    {
        $airline = Airline::factory()->create();
        $manager = $this->memberOf($airline, 'Manager');
        $pilot   = $this->memberOf($airline, 'Pilot');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $pilot), ['role' => 'Dispatcher'])
            ->assertRedirect();

        $this->assertSame('Dispatcher', $this->roleOf($pilot, $airline));
    }

    public function test_manager_can_remove_a_member(): void
    {
        $airline = Airline::factory()->create();
        $manager = $this->memberOf($airline, 'Manager');
        $pilot   = $this->memberOf($airline, 'Pilot');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->delete(route('members.destroy', $pilot))
            ->assertRedirect();

        $this->assertFalse($airline->isMember($pilot->fresh()));
    }

    public function test_last_manager_cannot_be_demoted(): void
    {
        $airline = Airline::factory()->create();
        $manager = $this->memberOf($airline, 'Manager');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $manager), ['role' => 'Pilot'])
            ->assertSessionHasErrors('role');

        $this->assertSame('Manager', $this->roleOf($manager, $airline));
    }

    public function test_last_manager_cannot_be_removed(): void
    {
        $airline = Airline::factory()->create();
        $manager = $this->memberOf($airline, 'Manager');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->delete(route('members.destroy', $manager))
            ->assertSessionHasErrors('member');

        $this->assertTrue($airline->isMember($manager->fresh()));
    }

    public function test_manager_can_be_demoted_when_another_manager_exists(): void
    {
        $airline = Airline::factory()->create();
        $manager = $this->memberOf($airline, 'Manager');
        $other   = $this->memberOf($airline, 'Manager');

        $this->actingAs($other)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $manager), ['role' => 'Pilot'])
            ->assertRedirect();

        $this->assertSame('Pilot', $this->roleOf($manager, $airline));
    }

    public function test_non_manager_cannot_access_members(): void
    {
        $airline = Airline::factory()->create();
        $pilot   = $this->memberOf($airline, 'Pilot');

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('members.index'))
            ->assertForbidden();
    }

    public function test_manager_cannot_update_member_of_another_airline(): void
    {
        $airline = Airline::factory()->create();
        $manager = $this->memberOf($airline, 'Manager');

        $otherAirline = Airline::factory()->create();
        $outsider     = $this->memberOf($otherAirline, 'Pilot');

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->put(route('members.update', $outsider), ['role' => 'Manager'])
            ->assertForbidden();
    }
}
