<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\InviteCode;
use App\Models\User;
use App\Notifications\InviteCodeRedeemed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

    public function test_redeeming_a_code_notifies_its_creator(): void
    {
        Notification::fake();

        $airline = Airline::factory()->create();
        $creator = User::factory()->create();
        $code = InviteCode::factory()->create([
            'airline_id' => $airline->id,
            'created_by' => $creator->id,
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('portal.redeem'), ['code' => $code->code])
            ->assertRedirect(route('dashboard'));

        Notification::assertSentTo(
            $creator,
            InviteCodeRedeemed::class,
            fn (InviteCodeRedeemed $notification) => $notification->inviteCode->is($code)
        );
        Notification::assertNotSentTo($user, InviteCodeRedeemed::class);
    }

    public function test_a_refused_redemption_does_not_notify_the_creator(): void
    {
        Notification::fake();

        $airline = Airline::factory()->create();
        $creator = User::factory()->create();
        $code = InviteCode::factory()
            ->usedBy(User::factory()->create())
            ->create(['airline_id' => $airline->id, 'created_by' => $creator->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('portal.redeem'), ['code' => $code->code])
            ->assertSessionHasErrors('code');

        Notification::assertNothingSent();
    }

    public function test_the_notification_payload_names_the_new_member(): void
    {
        $airline = Airline::factory()->create();
        $creator = User::factory()->create();
        $code = InviteCode::factory()->create([
            'airline_id' => $airline->id,
            'created_by' => $creator->id,
            'role' => 'Pilot',
        ]);
        $user = User::factory()->create(['name' => 'Jane Doe']);

        $this->actingAs($user)->post(route('portal.redeem'), ['code' => $code->code]);

        $notification = $creator->fresh()->unreadNotifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame('Invite Code Redeemed', $notification->data['title']);
        $this->assertStringContainsString('Jane Doe', $notification->data['message']);
        $this->assertStringContainsString($code->code, $notification->data['message']);
    }
}
