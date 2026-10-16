<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Reviewing on the PIREP detail page: the panel is for reviewers only, a
 * rejection always carries a reason (web and API), the decision records who
 * made it, and the reviewer moves straight on to the next pending PIREP.
 */
class ReviewPanelTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
    }

    public function test_panel_shows_for_reviewers_only(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $dispatcher = $this->memberOf($airline, 'Dispatcher');
        $flight = Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        $this->actingAs($dispatcher)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewflight', $flight))
            ->assertOk()
            ->assertSee('Review this PIREP')
            ->assertSee('Groundspeed')
            ->assertSee(route('flightreviewaccept', $flight), false);

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewflight', $flight))
            ->assertOk()
            ->assertDontSee('Review this PIREP')
            ->assertSee('Awaiting review')
            ->assertDontSee(route('flightreviewaccept', $flight), false);
    }

    public function test_web_reject_requires_a_reason(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $dispatcher = $this->memberOf($airline, 'Dispatcher');
        $flight = Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        $this->actingAs($dispatcher)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightreviewreject', $flight), ['rejection_remarks' => ''])
            ->assertSessionHasErrors('rejection_remarks');

        $this->assertSame(1, $flight->fresh()->status_id);
    }

    public function test_api_reject_requires_a_reason(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $dispatcher = $this->memberOf($airline, 'Dispatcher');
        $flight = Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        Sanctum::actingAs($dispatcher);

        $this->postJson("/api/v1/flights/{$flight->id}/reject")
            ->assertStatus(422)
            ->assertJsonValidationErrors('rejection_remarks');

        $this->postJson("/api/v1/flights/{$flight->id}/reject", ['rejection_remarks' => 'Wrong aircraft.'])
            ->assertOk()
            ->assertJsonPath('data.reviewedBy.id', $dispatcher->id);
    }

    public function test_accept_records_the_reviewer_and_opens_the_next_pending_pirep(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $dispatcher = $this->memberOf($airline, 'Dispatcher');
        $first = Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id, 'created_at' => now()->subHour()]);
        // The dispatcher's own PIREP is skipped: they may not review it
        Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $dispatcher->id, 'created_at' => now()->subMinutes(30)]);
        $next = Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id, 'created_at' => now()]);

        $this->actingAs($dispatcher)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightreviewaccept', $first))
            ->assertRedirect(route('viewflight', $next));

        $first->refresh();
        $this->assertSame(2, $first->status_id);
        $this->assertSame($dispatcher->id, $first->reviewed_by);
        $this->assertNotNull($first->reviewed_at);

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $airline])
            ->get(route('viewflight', $first))
            ->assertSee('Accepted by '.$dispatcher->name);
    }

    public function test_reject_returns_to_the_queue_when_nothing_is_left(): void
    {
        $airline = Airline::factory()->create();
        $pilot = $this->memberOf($airline, 'Pilot');
        $manager = $this->memberOf($airline, 'Manager');
        $flight = Flight::factory()->create(['airline_id' => $airline->id, 'pilot_id' => $pilot->id]);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $airline])
            ->post(route('flightreviewreject', $flight), ['rejection_remarks' => 'Wrong aircraft.'])
            ->assertRedirect(route('flightreviewindex'))
            ->assertSessionHas('success');

        $flight->refresh();
        $this->assertSame(3, $flight->status_id);
        $this->assertSame($manager->id, $flight->reviewed_by);
    }
}
