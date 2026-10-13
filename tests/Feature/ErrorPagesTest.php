<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Branded error pages (resources/views/errors): rendered with the instance
 * name in the title, and never replacing JSON errors on the API.
 */
class ErrorPagesTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.debug' => false]);
        User::factory()->create(); // otherwise EnsureSetupComplete redirects to /setup

        Route::middleware('web')->group(function () {
            Route::get('/_test/403', fn () => abort(403, 'Only managers may do this.'));
            Route::get('/_test/403-plain', fn () => abort(403));
            Route::get('/_test/419', fn () => throw new TokenMismatchException('CSRF token mismatch.'));
            Route::get('/_test/500', fn () => throw new RuntimeException('boom'));
            Route::get('/_test/503', fn () => abort(503));
            Route::get('/_test/429', fn () => abort(429));
            Route::get('/_test/418', fn () => abort(418));
            Route::get('/_test/502', fn () => abort(502));
        });
    }

    public function test_unknown_url_renders_branded_404(): void
    {
        $this->get('/does-not-exist')
            ->assertNotFound()
            ->assertSee('<title>Page Not Found - '.config('app.name').'</title>', false)
            ->assertSee('Back to homepage')
            ->assertSee(url('/'), false);
    }

    public function test_403_shows_the_abort_message(): void
    {
        $this->get('/_test/403')
            ->assertForbidden()
            ->assertSee('Access Denied')
            ->assertSee('Only managers may do this.');
    }

    public function test_403_without_message_falls_back_to_generic_text(): void
    {
        $this->get('/_test/403-plain')
            ->assertForbidden()
            ->assertSee('permission to access this page');
    }

    public function test_419_offers_reload_and_sign_in(): void
    {
        $this->get('/_test/419')
            ->assertStatus(419)
            ->assertSee('Page Expired')
            ->assertSee('Reload page')
            ->assertSee(url('/login'), false);
    }

    public function test_500_hides_the_exception_and_omits_support_link_by_default(): void
    {
        $this->get('/_test/500')
            ->assertServerError()
            ->assertSee('Server Error')
            ->assertDontSee('boom')
            ->assertDontSee('mailto:', false);
    }

    public function test_500_shows_support_email_when_configured(): void
    {
        Setting::set('support_email', 'ops@example.com');

        $this->get('/_test/500')
            ->assertServerError()
            ->assertSee('mailto:ops@example.com', false);
    }

    public function test_framework_default_codes_are_branded_too(): void
    {
        $this->get('/_test/429')->assertStatus(429)->assertSee('Too Many Requests')->assertSee('error-code', false);
        $this->get('/_test/503')->assertStatus(503)->assertSee('Service Unavailable')->assertSee('bi-tools', false);
    }

    public function test_other_codes_use_the_4xx_and_5xx_fallbacks(): void
    {
        $this->get('/_test/418')->assertStatus(418)->assertSee('Request Error');
        $this->get('/_test/502')->assertStatus(502)->assertSee('Service Unavailable');
    }

    public function test_api_errors_stay_json(): void
    {
        $this->getJson('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertDontSee('<html', false);
    }
}
