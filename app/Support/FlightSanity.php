<?php

namespace App\Support;

use App\Models\Flight;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

/**
 * Physics-grounded sanity checks shared by both PIREP filing paths (the web
 * FlightController and the API StoreFlightRequest), so the rules live in one
 * place. Fuel bounds are plain field rules; the timing and altitude checks are
 * cross-field / model-aware and live here.
 */
class FlightSanity
{
    /**
     * Small grace to absorb clock skew between an ACARS/browser client and the
     * server (and the minute-truncated form prefill). Gross future timestamps -
     * the "planning a flight that hasn't happened" case - are still rejected.
     */
    private const FUTURE_TOLERANCE_MINUTES = 5;

    /**
     * Validate the block-off/block-on pair. Returns a human-readable error when
     * the pair is invalid, or null when it is within bounds. Checked in order:
     * block-on strictly after block-off, block-on not in the future (a PIREP
     * records a flight that already happened - YAAMS has no flight planning),
     * and the duration within Flight::MAX_DURATION_MINUTES.
     *
     * Flight times are Zulu (UTC), so both the input and "now" are compared in
     * UTC. Both the API (`Y-m-d H:i:s`) and the web `datetime-local` input
     * (`Y-m-d\TH:i`, no seconds) formats are accepted via Carbon::parse().
     */
    public static function timingError(?string $blockoff, ?string $blockon): ?string
    {
        if ($blockoff === null || $blockon === null) {
            return null;
        }

        try {
            $off = Carbon::parse($blockoff, 'UTC');
            $on = Carbon::parse($blockon, 'UTC');
        } catch (InvalidFormatException) {
            // Unparseable timestamps are the field rules' job to report.
            return null;
        }

        if ($on->lessThanOrEqualTo($off)) {
            return 'Block on time must be after block off time.';
        }

        if ($on->greaterThan(Carbon::now('UTC')->addMinutes(self::FUTURE_TOLERANCE_MINUTES))) {
            return 'Block on time cannot be in the future - a PIREP records a flight that already happened.';
        }

        $maxHours = intdiv(Flight::MAX_DURATION_MINUTES, 60);

        if ($off->diffInMinutes($on) > Flight::MAX_DURATION_MINUTES) {
            return "Flight duration exceeds the maximum of {$maxHours} hours.";
        }

        return null;
    }

    /**
     * Reject a cruise altitude above the aircraft's service ceiling. The ceiling
     * is optional per airframe (nullable column): when it is not set, no
     * per-aircraft limit applies and only the global crzalt field rule governs.
     * Returns a human-readable error or null.
     */
    public static function altitudeError(?int $crzalt, ?int $serviceCeiling): ?string
    {
        if ($crzalt === null || $serviceCeiling === null) {
            return null;
        }

        if ($crzalt > $serviceCeiling) {
            return 'Cruise altitude exceeds the service ceiling of this aircraft ('
                . number_format($serviceCeiling) . ' ft).';
        }

        return null;
    }
}
