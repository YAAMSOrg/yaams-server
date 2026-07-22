<?php

namespace Tests\Unit;

use App\Support\FlightSanity;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Pure timing/altitude sanity logic - no database involved. Dates are relative
 * to now so the "in the past / not in the future" checks are stable whenever
 * the suite runs.
 */
class FlightSanityTest extends TestCase
{
    private function ago(int $days): Carbon
    {
        return Carbon::now('UTC')->subDays($days)->startOfHour();
    }

    public function test_a_normal_flight_passes(): void
    {
        $off = $this->ago(2);
        $on = $off->copy()->addMinutes(90);

        $this->assertNull(FlightSanity::timingError($off->format('Y-m-d H:i:s'), $on->format('Y-m-d H:i:s')));
    }

    public function test_the_web_datetime_local_format_is_accepted(): void
    {
        // datetime-local inputs post `Y-m-d\TH:i` with no seconds.
        $off = $this->ago(2);
        $on = $off->copy()->addMinutes(90);

        $this->assertNull(FlightSanity::timingError($off->format('Y-m-d\TH:i'), $on->format('Y-m-d\TH:i')));
    }

    public function test_exactly_the_max_duration_passes(): void
    {
        // 26 hours on the nose is allowed.
        $off = $this->ago(3);
        $on = $off->copy()->addHours(26);

        $this->assertNull(FlightSanity::timingError($off->format('Y-m-d H:i:s'), $on->format('Y-m-d H:i:s')));
    }

    public function test_one_minute_over_the_max_duration_is_rejected(): void
    {
        $off = $this->ago(3);
        $on = $off->copy()->addHours(26)->addMinute();

        $this->assertNotNull(FlightSanity::timingError($off->format('Y-m-d H:i:s'), $on->format('Y-m-d H:i:s')));
    }

    public function test_block_on_before_block_off_is_rejected(): void
    {
        $off = $this->ago(2);
        $on = $off->copy()->subMinutes(90);

        $this->assertNotNull(FlightSanity::timingError($off->format('Y-m-d H:i:s'), $on->format('Y-m-d H:i:s')));
    }

    public function test_equal_block_times_are_rejected(): void
    {
        $off = $this->ago(2);

        $this->assertNotNull(FlightSanity::timingError($off->format('Y-m-d H:i:s'), $off->format('Y-m-d H:i:s')));
    }

    public function test_a_flight_in_the_future_is_rejected(): void
    {
        $off = Carbon::now('UTC')->addDays(2);
        $on = $off->copy()->addMinutes(90);

        $this->assertNotNull(FlightSanity::timingError($off->format('Y-m-d H:i:s'), $on->format('Y-m-d H:i:s')));
    }

    public function test_missing_timestamps_defer_to_the_field_rules(): void
    {
        $this->assertNull(FlightSanity::timingError(null, '2026-07-11 11:30:00'));
        $this->assertNull(FlightSanity::timingError('2026-07-11 10:00:00', null));
    }

    public function test_altitude_within_the_service_ceiling_passes(): void
    {
        $this->assertNull(FlightSanity::altitudeError(35000, 41000));
    }

    public function test_altitude_above_the_service_ceiling_is_rejected(): void
    {
        $this->assertNotNull(FlightSanity::altitudeError(45000, 41000));
    }

    public function test_altitude_check_is_skipped_when_no_ceiling_is_set(): void
    {
        $this->assertNull(FlightSanity::altitudeError(45000, null));
    }
}
