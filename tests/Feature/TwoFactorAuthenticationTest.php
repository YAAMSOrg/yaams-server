<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Opt-in TOTP two-factor authentication (Fortify). Covers enabling/confirming,
 * the login challenge (authenticator code + recovery code), and the parity fix
 * that the active airline is still seeded when a user logs in via 2FA.
 */
class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    private function google2fa(): Google2FA
    {
        return app(Google2FA::class);
    }

    /** Give a user confirmed 2FA and return the plain-text TOTP secret. */
    private function confirmTwoFactorFor(User $user): string
    {
        $secret = $this->google2fa()->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['ABCDE-12345', 'FGHIJ-67890'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $secret;
    }

    public function test_enabling_generates_an_unconfirmed_secret(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('two-factor.enable'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at, '2FA must stay pending until confirmed');
    }

    public function test_confirming_with_a_valid_code_enables_two_factor(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('two-factor.enable'));
        $user->refresh();

        $code = $this->google2fa()->getCurrentOtp(decrypt($user->two_factor_secret));

        $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $code]);

        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_login_with_two_factor_enabled_redirects_to_the_challenge(): void
    {
        $user = User::factory()->create();
        $this->confirmTwoFactorFor($user);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
        $this->assertNotNull(session('login.id'), 'the pending user id must be stashed for the challenge');
    }

    public function test_challenge_completes_with_a_valid_authenticator_code(): void
    {
        $user = User::factory()->create();
        $secret = $this->confirmTwoFactorFor($user);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

        $this->post(route('two-factor.login'), [
            'code' => $this->google2fa()->getCurrentOtp($secret),
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_completes_with_a_recovery_code(): void
    {
        $user = User::factory()->create();
        $this->confirmTwoFactorFor($user);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

        $this->post(route('two-factor.login'), ['recovery_code' => 'ABCDE-12345']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_rejects_an_invalid_code(): void
    {
        $user = User::factory()->create();
        $this->confirmTwoFactorFor($user);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

        $this->post(route('two-factor.login'), ['code' => '000000']);

        $this->assertGuest();
    }

    public function test_two_factor_login_still_seeds_the_active_airline(): void
    {
        $airline = Airline::factory()->create();
        $user = $this->memberOf($airline, 'Manager');
        $secret = $this->confirmTwoFactorFor($user);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);
        $this->post(route('two-factor.login'), [
            'code' => $this->google2fa()->getCurrentOtp($secret),
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertEquals($airline->id, session('activeairline')->id);
    }

    public function test_security_page_shows_the_two_factor_section(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings.security'));

        $response->assertOk();
        $response->assertSee('Two-factor authentication');
        $response->assertSee('Enable two-factor authentication');
    }

    public function test_security_page_renders_the_qr_code_during_setup(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('two-factor.enable'));

        $response = $this->actingAs($user)->get(route('settings.security'));

        $response->assertOk();
        $response->assertSee('<svg', false); // twoFactorQrCodeSvg() rendered
        $response->assertSee('Confirm');
    }

    public function test_security_page_renders_recovery_codes_when_enabled(): void
    {
        $user = User::factory()->create();
        $this->confirmTwoFactorFor($user);

        $response = $this->actingAs($user)->get(route('settings.security'));

        $response->assertOk();
        $response->assertSee('Recovery codes');
        $response->assertSee('ABCDE-12345');
        $response->assertSee('Regenerate recovery codes');
    }

    public function test_disabling_clears_the_two_factor_columns(): void
    {
        $user = User::factory()->create();
        $this->confirmTwoFactorFor($user);

        $this->actingAs($user)->delete(route('two-factor.disable'));

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }
}
