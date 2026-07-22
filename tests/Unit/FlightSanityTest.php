<?php

namespace Tests\Unit;

use App\Support\FlightSanity;
use Tests\TestCase;

/**
 * Pure duration/ordering sanity logic - no database involved.
 */
class FlightSanityTest extends TestCase
{
    public function test_a_normal_flight_passes(): void
    {
        $this->assertNull(FlightSanity::durationError('2026-07-11 10:00:00', '2026-07-11 11:30:00'));
    }

    public function test_the_web_datetime_local_format_is_accepted(): void
    {
        // datetime-local inputs post `Y-m-d\TH:i` with no seconds.
        $this->assertNull(FlightSanity::durationError('2026-07-11T10:00', '2026-07-11T11:30'));
    }

    public function test_exactly_the_max_duration_passes(): void
    {
        // 26 hours on the nose is allowed.
        $this->assertNull(FlightSanity::durationError('2026-07-11 10:00:00', '2026-07-12 12:00:00'));
    }

    public function test_one_minute_over_the_max_duration_is_rejected(): void
    {
        $this->assertNotNull(FlightSanity::durationError('2026-07-11 10:00:00', '2026-07-12 12:01:00'));
    }

    public function test_block_on_before_block_off_is_rejected(): void
    {
        $this->assertNotNull(FlightSanity::durationError('2026-07-11 11:30:00', '2026-07-11 10:00:00'));
    }

    public function test_equal_block_times_are_rejected(): void
    {
        $this->assertNotNull(FlightSanity::durationError('2026-07-11 10:00:00', '2026-07-11 10:00:00'));
    }

    public function test_missing_timestamps_defer_to_the_field_rules(): void
    {
        $this->assertNull(FlightSanity::durationError(null, '2026-07-11 11:30:00'));
        $this->assertNull(FlightSanity::durationError('2026-07-11 10:00:00', null));
    }
}
