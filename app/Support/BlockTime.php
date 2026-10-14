<?php

namespace App\Support;

use Illuminate\Database\Connection;

/**
 * Driver-specific SQL for a flight's block time (block-on minus block-off).
 * There is no portable date difference in SQL, and tests run on SQLite while
 * production runs MariaDB/MySQL.
 */
class BlockTime
{
    /**
     * Whole seconds between the two (already qualified/wrapped) columns.
     * Integer on every driver, so equal durations sort as real ties.
     */
    public static function secondsSql(Connection $connection, string $blockoff = 'blockoff', string $blockon = 'blockon'): string
    {
        return match ($connection->getDriverName()) {
            'sqlite' => "(strftime('%s', {$blockon}) - strftime('%s', {$blockoff}))",
            'pgsql' => "extract(epoch from ({$blockon} - {$blockoff}))",
            default => "timestampdiff(second, {$blockoff}, {$blockon})",
        };
    }
}
