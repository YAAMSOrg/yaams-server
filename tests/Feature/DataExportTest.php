<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

class DataExportTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    public function test_guests_cannot_export(): void
    {
        $this->get(route('settings.export'))->assertRedirect(route('login'));
    }

    public function test_export_contains_own_data_and_no_secrets(): void
    {
        $this->seedReferenceData();
        $airline = Airline::factory()->create(['icao_callsign' => 'EXA']);
        $pilot = $this->memberOf($airline, 'Pilot');
        $pilot->forceFill([
            'two_factor_secret' => encrypt('TOPSECRET'),
            'two_factor_confirmed_at' => now(),
        ])->save();
        $pilot->createToken('ACARS');

        $other = $this->memberOf($airline, 'Pilot');
        $aircraft = Aircraft::factory()->create(['used_by' => $airline->id]);
        Flight::factory()->create(['airline_id' => $airline->id, 'aircraft_id' => $aircraft->id, 'pilot_id' => $pilot->id, 'status_id' => 2, 'remarks' => 'my own flight']);
        Flight::factory()->create(['airline_id' => $airline->id, 'aircraft_id' => $aircraft->id, 'pilot_id' => $other->id, 'status_id' => 2, 'remarks' => 'someone else']);

        $response = $this->actingAs($pilot)->get(route('settings.export'));

        $response->assertOk()->assertDownload();
        $json = $response->streamedContent();
        $data = json_decode($json, true);

        $this->assertSame($pilot->email, $data['profile']['email']);
        $this->assertTrue($data['profile']['two_factor_enabled']);
        $this->assertSame('EXA', $data['airline_memberships'][0]['icao']);
        $this->assertCount(1, $data['flights']);
        $this->assertSame('my own flight', $data['flights'][0]['remarks']);
        $this->assertSame('ACARS', $data['api_tokens'][0]['name']);

        $this->assertStringNotContainsString('someone else', $json);
        $this->assertStringNotContainsString($pilot->password, $json);
        $this->assertStringNotContainsString($pilot->two_factor_secret, $json);

        $this->assertTrue(Activity::where('event', 'user_data_exported')->sole()->causer->is($pilot));
    }

    public function test_privacy_settings_page_renders(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('settings.privacy'))
            ->assertOk()
            ->assertSee(route('settings.export'), false);
    }
}
