<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\InviteCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Redeeming an invite code joins the user to the airline with the code's role,
 * and codes are single-use.
 */
class InviteCodeRedemptionTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_a_valid_code_joins_the_user_with_its_role_and_is_marked_used(): void
    {
        $airline = Airline::factory()->create();
        $code = InviteCode::factory()->create([
            'airline_id' => $airline->id,
            'role' => 'Dispatcher',
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('portal.redeem'), ['code' => $code->code])
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->isMemberOf($airline));
        $this->assertTrue($user->fresh()->hasAirlineRole($airline, 'Dispatcher'));
        $this->assertSame($user->id, $code->fresh()->used_by);
    }

    public function test_an_already_used_code_is_refused(): void
    {
        $airline = Airline::factory()->create();
        $code = InviteCode::factory()
            ->usedBy(User::factory()->create())
            ->create(['airline_id' => $airline->id]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('portal.redeem'), ['code' => $code->code])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->isMemberOf($airline));
    }
}
