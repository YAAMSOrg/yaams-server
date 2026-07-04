<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Verbosity levels for the activity log. Every activity carries a level; the
 * instance-wide `LOG_LEVEL` setting is a threshold — activities whose level is
 * below the threshold are dropped before they hit the database (see the
 * Activity::saving hook in AppServiceProvider).
 *
 *   debug   -> automatic model create/update/delete (noisiest)
 *   info    -> meaningful user actions (login, PIREP filed/reviewed, invite redeemed, ...)
 *   warning -> security-relevant events (failed login, ...)
 */
class ActivityLevel
{
    public const DEBUG = 'debug';
    public const INFO = 'info';
    public const WARNING = 'warning';

    /** Numeric weight per level; higher = more severe / less noisy. */
    public const LEVELS = [
        self::DEBUG => 10,
        self::INFO => 20,
        self::WARNING => 30,
    ];

    public static function weight(string $level): int
    {
        return self::LEVELS[$level] ?? self::LEVELS[self::DEBUG];
    }

    /** Numeric weight of the configured `LOG_LEVEL` threshold. */
    public static function threshold(): int
    {
        return self::weight(Setting::get('LOG_LEVEL', self::DEBUG));
    }

    /** Whether an activity at the given level should be recorded. */
    public static function shouldRecord(string $level): bool
    {
        return self::weight($level) >= self::threshold();
    }
}
