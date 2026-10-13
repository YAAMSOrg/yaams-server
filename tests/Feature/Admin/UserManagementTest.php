<?php

namespace Tests\Feature\Admin;

use App\Models\Airline;
use App\Models\Flight;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Notifications\TwoFactorReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    private function enableTwoFactor(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => encrypt('SECRET'),
            'two_factor_recovery_codes' => encrypt(json_encode(['ABCDE-12345'])),
            'two_factor_confirmed_at' => now(),
        ])->save();
    }

    private function suspend(User $user): void
    {
        $user->forceFill(['suspended_at' => now()])->save();
    }

    public function test_non_admins_are_forbidden(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.show', $other))->assertForbidden();
        $this->actingAs($user)->post(route('admin.users.suspend', $other))->assertForbidden();

        $this->assertFalse($other->fresh()->isSuspended());
    }

    public function test_index_lists_searches_and_filters_users(): void
    {
        User::factory()->create(['name' => 'Alice Pilot', 'email' => 'alice@example.com']);
        $bob = User::factory()->create(['name' => 'Bob Banned', 'email' => 'bob@example.com']);
        $this->suspend($bob);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['q' => 'alice']))
            ->assertOk()
            ->assertSee('alice@example.com')
            ->assertDontSee('bob@example.com');

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['status' => 'suspended']))
            ->assertOk()
            ->assertSee('bob@example.com')
            ->assertDontSee('alice@example.com');
    }

    public function test_show_renders_the_user_detail_page(): void
    {
        $airline = Airline::factory()->create();
        $user = $this->ownerOf($airline);

        $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee($airline->name)
            ->assertSee('Owner');
    }

    public function test_admin_can_suspend_and_reactivate_a_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.users.suspend', $user), ['reason' => 'Spam'])
            ->assertRedirect(route('admin.users.show', $user));

        $user->refresh();
        $this->assertTrue($user->isSuspended());
        $this->assertSame('Spam', $user->suspension_reason);
        $this->assertDatabaseHas('activity_log', [
            'event' => 'user_suspended',
            'causer_id' => $this->admin->id,
            'subject_id' => $user->id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.users.reactivate', $user))
            ->assertRedirect(route('admin.users.show', $user));

        $this->assertFalse($user->fresh()->isSuspended());
        $this->assertNull($user->fresh()->suspension_reason);
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        $user = User::factory()->create();
        $this->suspend($user);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'This account has been suspended.']);

        $this->assertGuest();
        $this->assertDatabaseHas('activity_log', ['event' => 'login_suspended', 'subject_id' => $user->id]);
    }

    public function test_suspended_user_with_two_factor_is_refused_before_the_challenge(): void
    {
        $user = User::factory()->create();
        $this->enableTwoFactor($user);
        $this->suspend($user);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertNull(session('login.id'));
    }

    public function test_wrong_password_still_fails_with_generic_message(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->assertGuest();
    }

    public function test_active_user_can_still_log_in(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_live_session_of_suspended_user_is_ended(): void
    {
        $user = User::factory()->create();
        $this->suspend($user);

        $this->actingAs($user)
            ->get(route('settings.profile'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_api_tokens_of_suspended_user_stop_working(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('acars')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/user')->assertOk();

        $this->suspend($user);
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/v1/user')->assertUnauthorized();
        $this->assertSame(1, $user->tokens()->count(), 'tokens are kept for reactivation');
    }

    public function test_admin_cannot_suspend_themselves(): void
    {
        $other = User::factory()->create();
        $other->assignRole('Super-Admin');

        $this->actingAs($this->admin)
            ->post(route('admin.users.suspend', $this->admin))
            ->assertSessionHasErrors('action');

        $this->assertFalse($this->admin->fresh()->isSuspended());
    }

    public function test_super_admin_role_can_be_granted_and_revoked(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.users.role', $user), ['super_admin' => 1])
            ->assertRedirect(route('admin.users.show', $user));
        $this->assertTrue($user->fresh()->hasRole('Super-Admin'));

        $this->actingAs($this->admin)
            ->put(route('admin.users.role', $user), ['super_admin' => 0])
            ->assertRedirect(route('admin.users.show', $user));
        $this->assertFalse($user->fresh()->hasRole('Super-Admin'));

        $this->assertDatabaseHas('activity_log', ['event' => 'super_admin_granted', 'subject_id' => $user->id]);
        $this->assertDatabaseHas('activity_log', ['event' => 'super_admin_revoked', 'subject_id' => $user->id]);
    }

    public function test_admin_cannot_revoke_their_own_super_admin_role(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.users.role', $this->admin), ['super_admin' => 0])
            ->assertSessionHasErrors('action');

        $this->assertTrue($this->admin->fresh()->hasRole('Super-Admin'));
    }

    public function test_admin_can_update_profile_and_verification(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'email_verified' => '1',
            ])
            ->assertRedirect(route('admin.users.show', $user));

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'email_verified' => '0',
            ]);
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_profile_update_rejects_duplicate_email(): void
    {
        $user = User::factory()->create();
        $taken = User::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $taken->email,
                'email_verified' => '1',
            ])
            ->assertSessionHasErrorsIn('profile', 'email');
    }

    public function test_admin_can_reset_two_factor(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->enableTwoFactor($user);

        $this->actingAs($this->admin)
            ->delete(route('admin.users.2fa.reset', $user))
            ->assertRedirect(route('admin.users.show', $user));

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        Notification::assertSentTo($user, TwoFactorReset::class);

        $activity = Activity::where('event', 'two_factor_reset')->first();
        $this->assertSame($this->admin->id, $activity->causer_id);
        $this->assertSame('admin', $activity->getExtraProperty('via'));
    }

    public function test_admin_can_send_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.users.password-reset', $user))
            ->assertRedirect(route('admin.users.show', $user));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_admin_can_revoke_all_api_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('one');
        $user->createToken('two');

        $this->actingAs($this->admin)
            ->delete(route('admin.users.tokens.revoke', $user))
            ->assertRedirect(route('admin.users.show', $user));

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_delete_requires_matching_email_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user), ['confirm_email' => 'wrong@example.com'])
            ->assertSessionHasErrorsIn('deleteUser', 'confirm_email');

        $this->assertModelExists($user);
    }

    public function test_delete_is_refused_for_airline_owner(): void
    {
        $airline = Airline::factory()->create();
        $owner = $this->ownerOf($airline);
        $this->memberOf($airline, 'Manager');

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $owner), ['confirm_email' => $owner->email])
            ->assertSessionHasErrors('action');

        $this->assertModelExists($owner);
    }

    public function test_delete_is_refused_for_sole_manager(): void
    {
        $airline = Airline::factory()->create();
        $manager = $this->memberOf($airline, 'Manager');

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $manager), ['confirm_email' => $manager->email])
            ->assertSessionHasErrors('action');

        $this->assertModelExists($manager);
    }

    public function test_admin_can_delete_a_user_and_flights_are_anonymized(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $flight = Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $pilot), ['confirm_email' => $pilot->email])
            ->assertRedirect(route('admin.users.index'));

        $this->assertModelMissing($pilot);
        $this->assertNull($flight->fresh()->pilot_id);
        $this->assertDatabaseHas('activity_log', ['event' => 'user_deleted_by_admin', 'causer_id' => $this->admin->id]);
    }

    public function test_admin_cannot_delete_themselves_here(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin), ['confirm_email' => $this->admin->email])
            ->assertSessionHasErrors('action');

        $this->assertModelExists($this->admin);
    }

    public function test_activity_log_can_be_filtered_by_user(): void
    {
        $user = User::factory()->create(['name' => 'Filtered Person']);

        $this->actingAs($this->admin)
            ->post(route('admin.users.suspend', $user));

        $this->actingAs($this->admin)
            ->get(route('admin.activity.index', ['user' => $user->id]))
            ->assertOk()
            ->assertSee('Filtered Person')
            ->assertSee('user_suspended');
    }
}
