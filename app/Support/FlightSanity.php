<?php

namespace App\Support;

use App\Models\Flight;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

/**
 * Physics-grounded sanity checks shared by both PIREP filing paths (the web
 * FlightController and the API StoreFlightRequest), so the rules live in one
 * place. Fuel bounds are plain field rules; the duration/ordering check is
 * cross-field and lives here.
 */
class FlightSanity
{
    /**
     * Validate the block-off/block-on pair. Returns a human-readable error
     * message when the pair is invalid (block-on not strictly after block-off,
     * or the duration exceeds Flight::MAX_DURATION_MINUTES), or null when it is
     * within bounds.
     *
     * Both the API (`Y-m-d H:i:s`) and the web `datetime-local` input
     * (`Y-m-d\TH:i`, no seconds) formats are accepted via Carbon::parse().
     */
    public static function durationError(?string $blockoff, ?string $blockon): ?string
    {
        if ($blockoff === null || $blockon === null) {
            return null;
        }

        try {
            $off = Carbon::parse($blockoff);
            $on = Carbon::parse($blockon);
        } catch (InvalidFormatException) {
            // Unparseable timestamps are the field rules' job to report.
            return null;
        }

        if ($on->lessThanOrEqualTo($off)) {
            return 'Block on time must be after block off time.';
        }

        $maxHours = intdiv(Flight::MAX_DURATION_MINUTES, 60);

        if ($off->diffInMinutes($on) > Flight::MAX_DURATION_MINUTES) {
            return "Flight duration exceeds the maximum of {$maxHours} hours.";
        }

        return null;
    }
}
