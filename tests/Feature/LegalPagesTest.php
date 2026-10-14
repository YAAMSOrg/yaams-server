<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

class LegalPagesTest extends TestCase
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

    private function legalPayload(array $overrides = []): array
    {
        return array_merge([
            'imprint_enabled' => '0',
            'imprint_content' => '',
            'privacy_content' => '',
            'terms_enabled'   => '0',
            'terms_content'   => '',
        ], $overrides);
    }

    public function test_imprint_and_terms_are_hidden_by_default(): void
    {
        $this->get(route('legal.imprint'))->assertNotFound();
        $this->get(route('legal.terms'))->assertNotFound();
    }

    public function test_enabled_pages_are_public_and_linked_in_the_footer(): void
    {
        Setting::set('imprint_enabled', '1');
        Setting::set('imprint_content', "**Operator**\n\nJane Doe, Example Street 1");

        $this->get(route('legal.imprint'))
            ->assertOk()
            ->assertSee('<strong>Operator</strong>', false)
            ->assertSee('Jane Doe');

        $this->get(route('home'))->assertSee(route('legal.imprint'), false);
        $this->get(route('login'))->assertSee(route('legal.imprint'), false);
        $this->get(route('home'))->assertDontSee(route('legal.terms'), false);
    }

    public function test_single_line_breaks_are_kept(): void
    {
        Setting::set('imprint_enabled', '1');
        Setting::set('imprint_content', "## Operator\n\nJane Doe\nExample Street 1\n12345 Example City");

        $this->get(route('legal.imprint'))
            ->assertOk()
            ->assertSee("<p>Jane Doe<br>\nExample Street 1<br>\n12345 Example City</p>", false);
    }

    public function test_an_enabled_page_without_content_stays_hidden(): void
    {
        Setting::set('terms_enabled', '1');

        $this->get(route('legal.terms'))->assertNotFound();
    }

    public function test_privacy_policy_falls_back_to_the_built_in_template(): void
    {
        Setting::set('app_name', 'Example VA');
        Setting::set('support_email', 'privacy@example.com');

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('default privacy policy shipped with YAAMS')
            ->assertSee('Example VA')
            ->assertSee('privacy@example.com');

        Setting::set('privacy_content', '## Our own policy');

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('Our own policy')
            ->assertDontSee('default privacy policy shipped with YAAMS');
    }

    public function test_raw_html_and_unsafe_links_are_stripped(): void
    {
        Setting::set('privacy_content', "<script>alert('x')</script>\n\n[click](javascript:alert(1))");

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertDontSee("<script>alert('x')</script>", false)
            ->assertDontSee('javascript:alert', false);
    }

    public function test_legal_pages_are_linked_from_error_pages(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee(route('legal.privacy'), false);
    }

    public function test_only_super_admins_can_edit_legal_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.legal.edit'))->assertForbidden();
        $this->actingAs($user)->put(route('admin.legal.update'), $this->legalPayload())->assertForbidden();
        $this->actingAs($this->admin)->get(route('admin.legal.edit'))->assertOk();
    }

    public function test_publishing_a_page_requires_content(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.legal.update'), $this->legalPayload(['imprint_enabled' => '1']))
            ->assertSessionHasErrors('imprint_content');

        $this->assertSame('0', Setting::get('imprint_enabled'));
    }

    public function test_saving_updates_content_dates_policies_and_logs(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.legal.update'), $this->legalPayload([
                'imprint_enabled' => '1',
                'imprint_content' => 'Jane Doe',
                'terms_enabled'   => '1',
                'terms_content'   => 'Be nice.',
            ]))
            ->assertRedirect(route('admin.legal.edit'));

        $this->assertSame('1', Setting::get('imprint_enabled'));
        $this->assertSame('Jane Doe', Setting::get('imprint_content'));
        $this->assertNull(Setting::get('privacy_content'));
        $this->assertNotNull(Setting::get('legal_updated_at'));
        $this->get(route('legal.terms'))->assertOk()->assertSee('Be nice.');

        $activity = Activity::where('event', 'legal_pages_updated')->sole();
        $this->assertTrue($activity->causer->is($this->admin));
    }

    public function test_imprint_only_changes_do_not_date_the_policies(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.legal.update'), $this->legalPayload([
                'imprint_enabled' => '1',
                'imprint_content' => 'Jane Doe',
            ]));

        $this->assertNull(Setting::get('legal_updated_at'));
    }

    public function test_registration_requires_accepting_the_privacy_policy(): void
    {
        $payload = [
            'name' => 'New Pilot',
            'email' => 'new@test.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ];

        $this->post(route('register'), $payload)->assertSessionHasErrors('accept_legal');
        $this->assertDatabaseMissing('users', ['email' => 'new@test.com']);

        $this->post(route('register'), $payload + ['accept_legal' => '1'])->assertRedirect();
        $this->assertNotNull(User::where('email', 'new@test.com')->sole()->legal_accepted_at);
    }

    public function test_register_form_mentions_terms_only_when_published(): void
    {
        $this->get(route('register'))->assertDontSee(route('legal.terms'), false);

        Setting::set('terms_enabled', '1');
        Setting::set('terms_content', 'Be nice.');

        $this->get(route('register'))->assertSee(route('legal.terms'), false);
    }

    public function test_api_info_exposes_published_legal_urls(): void
    {
        Setting::set('imprint_enabled', '1');
        Setting::set('imprint_content', 'Jane Doe');

        $this->getJson('/api/v1/info')
            ->assertOk()
            ->assertJsonPath('data.legal.imprintUrl', route('legal.imprint'))
            ->assertJsonPath('data.legal.privacyUrl', route('legal.privacy'))
            ->assertJsonPath('data.legal.termsUrl', null);
    }
}
