<?php

namespace Tests\Feature;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\User;
use App\Notifications\PirepAccepted;
use App\Notifications\PirepRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\SeedsDomain;
use Tests\TestCase;

/**
 * Web PIREP review: who may accept/reject, the "no reviewing your own PIREP"
 * rule for Dispatchers, and the pilot notification on each decision.
 */
class FlightReviewTest extends TestCase
{
    use RefreshDatabase, SeedsDomain;

    protected Airline $airline;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedReferenceData();
        $this->airline = Airline::factory()->create();
    }

    private function pendingFlightBy(User $pilot): Flight
    {
        $aircraft = Aircraft::factory()->create(['used_by' => $this->airline->id]);

        return Flight::factory()->create([
            'airline_id' => $this->airline->id,
            'aircraft_id' => $aircraft->id,
            'pilot_id' => $pilot->id,
            'status_id' => 1,
        ]);
    }

    public function test_a_pilot_cannot_review_flights(): void
    {
        Notification::fake();

        $pilot = $this->memberOf($this->airline, 'Pilot');
        $flight = $this->pendingFlightBy($pilot);

        $this->actingAs($pilot)
            ->withSession(['activeairline' => $this->airline])
            ->post(route('flightreviewaccept', $flight))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(1, $flight->fresh()->status_id);
        Notification::assertNothingSent();
    }

    public function test_a_dispatcher_cannot_accept_their_own_pirep(): void
    {
        $dispatcher = $this->memberOf($this->airline, 'Dispatcher');
        $flight = $this->pendingFlightBy($dispatcher);

        $this->actingAs($dispatcher)
            ->withSession(['activeairline' => $this->airline])
            ->post(route('flightreviewaccept', $flight))
            ->assertRedirect(route('flightreviewindex'));

        $this->assertSame(1, $flight->fresh()->status_id);
    }

    public function test_a_manager_accepts_a_pirep_and_the_pilot_is_notified(): void
    {
        Notification::fake();

        $pilot = $this->memberOf($this->airline, 'Pilot');
        $manager = $this->memberOf($this->airline, 'Manager');
        $flight = $this->pendingFlightBy($pilot);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $this->airline])
            ->post(route('flightreviewaccept', $flight))
            ->assertRedirect(route('flightreviewindex'));

        $this->assertSame(2, $flight->fresh()->status_id);
        Notification::assertSentTo($pilot, PirepAccepted::class);
    }

    public function test_a_manager_rejects_a_pirep_and_the_pilot_is_notified(): void
    {
        Notification::fake();

        $pilot = $this->memberOf($this->airline, 'Pilot');
        $manager = $this->memberOf($this->airline, 'Manager');
        $flight = $this->pendingFlightBy($pilot);

        $this->actingAs($manager)
            ->withSession(['activeairline' => $this->airline])
            ->post(route('flightreviewreject', $flight), ['rejection_remarks' => 'Cruise altitude too high.'])
            ->assertRedirect(route('flightreviewindex'));

        $this->assertSame(3, $flight->fresh()->status_id);
        Notification::assertSentTo($pilot, PirepRejected::class);
    }
}
