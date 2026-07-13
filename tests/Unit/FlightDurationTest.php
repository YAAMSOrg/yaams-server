<?php

namespace Tests\Unit;

use App\Models\Flight;
use Tests\TestCase;

/**
 * Pure computed-attribute logic on Flight - no database involved.
 */
class FlightDurationTest extends TestCase
{
    private function flightBetween(string $blockoff, string $blockon): Flight
    {
        $flight = new Flight();
        $flight->blockoff = $blockoff;
        $flight->blockon = $blockon;

        return $flight;
    }

    public function test_duration_in_minutes_is_the_block_time_difference(): void
    {
        $flight = $this->flightBetween('2026-07-11 10:00:00', '2026-07-11 11:30:00');

        $this->assertSame(90, $flight->flight_duration_minutes);
    }

    public function test_duration_counts_hours_across_the_full_block(): void
    {
        $flight = $this->flightBetween('2026-07-11 08:15:00', '2026-07-11 12:45:00');

        $this->assertSame(270, $flight->flight_duration_minutes);
    }

    public function test_duration_is_formatted_as_hours_and_minutes(): void
    {
        $flight = $this->flightBetween('2026-07-11 10:00:00', '2026-07-11 11:30:00');

        $this->assertSame('01:30 h', $flight->flight_duration);
    }

    public function test_unparseable_times_return_an_error_string(): void
    {
        $flight = $this->flightBetween('not-a-date', 'also-not-a-date');

        $this->assertSame('Error while parsing flight duration time.', $flight->flight_duration_minutes);
    }
}
