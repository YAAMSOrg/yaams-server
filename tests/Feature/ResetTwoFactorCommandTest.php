<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TwoFactorReset;
use App\Support\ActivityLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * `yaams:reset-2fa` lets an instance admin recover an account whose owner lost
 * both the authenticator and the recovery codes. The user is informed by mail.
 */
class ResetTwoFactorCommandTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        Notification::fake();
    }

    private function userWithTwoFactor(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $user->forceFill([
            'two_factor_secret' => encrypt('SECRETSECRETSECR'),
            'two_factor_recovery_codes' => encrypt(json_encode(['ABCDE-12345', 'FGHIJ-67890'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user;
    }

    public function test_reset_removes_two_factor_and_notifies_the_user(): void
    {
        $user = $this->userWithTwoFactor();

        $this->artisan('yaams:reset-2fa', ['email' => $user->email])
            ->expectsConfirmation('Remove two-factor authentication from this account? The user will be informed by e-mail.', 'yes')
            ->assertSuccessful();

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);

        Notification::assertSentTo($user, TwoFactorReset::class);

        $activity = Activity::where('event', 'two_factor_reset')->first();
        $this->assertNotNull($activity);
        $this->assertSame(ActivityLevel::WARNING, $activity->level);
        $this->assertTrue($activity->subject->is($user));
        $this->assertNull($activity->causer_id);
    }

    public function test_force_skips_the_confirmation(): void
    {
        $user = $this->userWithTwoFactor();

        $this->artisan('yaams:reset-2fa', ['email' => $user->email, '--force' => true])
            ->assertSuccessful();

        $this->assertNull($user->fresh()->two_factor_secret);
        Notification::assertSentTo($user, TwoFactorReset::class);
    }

    public function test_declining_the_confirmation_changes_nothing(): void
    {
        $user = $this->userWithTwoFactor();

        $this->artisan('yaams:reset-2fa', ['email' => $user->email])
            ->expectsConfirmation('Remove two-factor authentication from this account? The user will be informed by e-mail.', 'no')
            ->assertFailed();

        $this->assertNotNull($user->fresh()->two_factor_secret);
        Notification::assertNothingSent();
    }

    public function test_unknown_email_fails(): void
    {
        $this->artisan('yaams:reset-2fa', ['email' => 'nobody@test.com', '--force' => true])
            ->assertFailed();

        Notification::assertNothingSent();
    }

    public function test_user_without_two_factor_is_left_alone(): void
    {
        $user = User::factory()->create();

        $this->artisan('yaams:reset-2fa', ['email' => $user->email, '--force' => true])
            ->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertNull(Activity::where('event', 'two_factor_reset')->first());
    }

    public function test_mail_is_sent_even_with_email_notifications_disabled(): void
    {
        $user = $this->userWithTwoFactor(['email_notifications' => false]);

        $this->artisan('yaams:reset-2fa', ['email' => $user->email, '--force' => true])
            ->assertSuccessful();

        Notification::assertSentTo($user, TwoFactorReset::class, fn ($notification, array $channels) => in_array('mail', $channels, true));
    }

    public function test_user_can_log_in_with_password_only_after_reset(): void
    {
        $user = $this->userWithTwoFactor();

        $this->artisan('yaams:reset-2fa', ['email' => $user->email, '--force' => true])
            ->assertSuccessful();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }
}
