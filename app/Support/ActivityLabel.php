<?php

namespace App\Support;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\InviteCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Human-friendly one-line label for an activity-log subject, e.g. an aircraft
 * renders as "D-EXAM (FLY)" (registration + operating airline ICAO callsign)
 * rather than "Aircraft #1". Returns null when there's no meaningful label, so
 * callers can fall back to the class name + id.
 */
class ActivityLabel
{
    public static function for(?Model $subject): ?string
    {
        return match (true) {
            $subject instanceof Aircraft => trim($subject->registration
                . ($subject->airline ? ' (' . $subject->airline->icao_callsign . ')' : '')),
            $subject instanceof Flight => $subject->full_icao_callsign,
            $subject instanceof Airline => $subject->name . ' (' . $subject->icao_callsign . ')',
            $subject instanceof InviteCode => $subject->code,
            $subject instanceof User => $subject->name,
            default => null,
        };
    }
}
