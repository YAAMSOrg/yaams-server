<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Instance display-timezone helper.
 *
 * The database stores every timestamp in UTC. This converts to/from the
 * configured instance display timezone (the `timezone` setting) for admin- and
 * crew-facing datetimes such as NOTAM expiry.
 *
 * Flight/PIREP times are aviation data and are intentionally NOT routed through
 * this helper — they always stay in UTC (Zulu), the international standard.
 */
class Timezone
{
    /**
     * The configured instance display timezone (falls back to the app timezone).
     */
    public static function current(): string
    {
        return Setting::get('timezone', config('app.timezone'));
    }

    /**
     * Interpret a naive datetime string (e.g. from an
     * `<input type="datetime-local">`) as the instance display timezone and
     * convert it to a UTC Carbon instance for storage.
     */
    public static function toUtc(?string $local): ?Carbon
    {
        if (empty($local)) {
            return null;
        }

        return Carbon::parse($local, self::current())->utc();
    }

    /**
     * Render a stored UTC datetime in the instance display timezone.
     */
    public static function format(?CarbonInterface $utc, string $format = 'Y-m-d H:i'): ?string
    {
        return $utc?->copy()->setTimezone(self::current())->format($format);
    }
}
