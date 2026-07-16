<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * The Prometheus /metrics endpoint: the bearer-token gate (METRICS_TOKEN)
 * and the scrape-time domain gauges.
 */
class MetricsEndpointTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    private const TOKEN = 'test-metrics-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.metrics.token' => self::TOKEN]);
    }

    public function test_requests_without_a_token_are_rejected(): void
    {
        $this->get('/metrics')->assertForbidden();
    }

    public function test_requests_with_a_wrong_token_are_rejected(): void
    {
        $this->withToken('wrong-token')->get('/metrics')->assertForbidden();
    }

    public function test_the_endpoint_is_disabled_when_no_token_is_configured(): void
    {
        config(['services.metrics.token' => null]);

        $this->get('/metrics')->assertForbidden();
        $this->withToken('')->get('/metrics')->assertForbidden();
    }

    public function test_a_valid_token_returns_prometheus_text_format(): void
    {
        $response = $this->withToken(self::TOKEN)->get('/metrics');

        $response->assertOk();
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
        $response->assertSee('yaams_users_total', false);
        $response->assertSee('yaams_airlines_total', false);
        $response->assertSee('yaams_queue_size', false);
    }

    public function test_domain_gauges_report_current_counts(): void
    {
        $this->seedReferenceData();
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $aircraft = Aircraft::factory()->create([
            'used_by' => $airline->id,
            'status' => Aircraft::STATUS_ACTIVE,
        ]);
        Flight::factory()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'pilot_id' => $pilot->id,
            'status_id' => 1,
        ]);
        Flight::factory()->create([
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft->id,
            'pilot_id' => $pilot->id,
            'status_id' => 2,
        ]);

        $body = $this->withToken(self::TOKEN)->get('/metrics')->assertOk()->getContent();

        $this->assertStringContainsString('yaams_users_total ' . User::count(), $body);
        $this->assertStringContainsString('yaams_airlines_total 1', $body);
        $this->assertStringContainsString('yaams_flights_total{status="pending"} 1', $body);
        $this->assertStringContainsString('yaams_flights_total{status="accepted"} 1', $body);
        $this->assertStringContainsString('yaams_flights_total{status="rejected"} 0', $body);
        $this->assertStringContainsString('yaams_aircraft_total{status="active"} 1', $body);
        $this->assertStringContainsString('yaams_aircraft_total{status="retired"} 0', $body);
        $this->assertStringContainsString('yaams_queue_failed_jobs_total 0', $body);
    }
}
