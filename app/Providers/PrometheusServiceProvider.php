<?php

namespace App\Providers;

use App\Models\Aircraft;
use App\Models\AircraftImage;
use App\Models\Airline;
use App\Models\AirlineMembership;
use App\Models\Flight;
use App\Models\InviteCode;
use App\Models\Notam;
use App\Models\User;
use App\Support\ActivityLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Prometheus\Collectors\Queue\QueueOldestPendingJobCollector;
use Spatie\Prometheus\Collectors\Queue\QueueSizeCollector;
use Spatie\Prometheus\Facades\Prometheus;

class PrometheusServiceProvider extends ServiceProvider
{
    /**
     * flights.status_id is a hardcoded FK (see CLAUDE.md "Flight Status IDs").
     */
    private const FLIGHT_STATUSES = [
        1 => 'pending',
        2 => 'accepted',
        3 => 'rejected',
    ];

    private const AIRCRAFT_STATUSES = [
        Aircraft::STATUS_ACTIVE,
        Aircraft::STATUS_INACTIVE,
        Aircraft::STATUS_RETIRED,
    ];

    private const AIRCRAFT_IMAGE_STATUSES = [
        AircraftImage::STATUS_PENDING,
        AircraftImage::STATUS_APPROVED,
    ];

    /**
     * airline_memberships.role enum values.
     */
    private const MEMBERSHIP_ROLES = ['Pilot', 'Dispatcher', 'Manager'];

    /**
     * Windows for the "recently active users" gauge, label => seconds.
     */
    private const ACTIVE_USER_WINDOWS = [
        '15m' => 15 * 60,
        '24h' => 24 * 3600,
        '7d' => 7 * 24 * 3600,
        '30d' => 30 * 24 * 3600,
    ];

    /*
     * All gauges are evaluated fresh on every scrape of /metrics, so no
     * cross-request metric storage is involved. Keep every value() a cheap
     * aggregate query - the endpoint runs them all on each scrape.
     */
    public function register(): void
    {
        Prometheus::addGauge('Users total')
            ->name('users_total')
            ->helpText('Total number of registered users')
            ->value(fn () => User::count());

        Prometheus::addGauge('Airlines total')
            ->name('airlines_total')
            ->helpText('Total number of airlines')
            ->value(fn () => Airline::count());

        Prometheus::addGauge('Flights total')
            ->name('flights_total')
            ->label('status')
            ->helpText('Total number of filed flights (PIREPs) by review status')
            ->value(function () {
                $counts = Flight::query()
                    ->toBase()
                    ->selectRaw('status_id, count(*) as aggregate')
                    ->groupBy('status_id')
                    ->pluck('aggregate', 'status_id');

                return collect(self::FLIGHT_STATUSES)
                    ->map(fn (string $label, int $id) => [(int) ($counts[$id] ?? 0), [$label]])
                    ->values()
                    ->all();
            });

        Prometheus::addGauge('Aircraft total')
            ->name('aircraft_total')
            ->label('status')
            ->helpText('Total number of aircraft by lifecycle status')
            ->value(function () {
                $counts = Aircraft::query()
                    ->toBase()
                    ->selectRaw('status, count(*) as aggregate')
                    ->groupBy('status')
                    ->pluck('aggregate', 'status');

                return collect(self::AIRCRAFT_STATUSES)
                    ->map(fn (string $status) => [(int) ($counts[$status] ?? 0), [$status]])
                    ->all();
            });

        Prometheus::addGauge('Invite codes unused')
            ->name('invite_codes_unused_total')
            ->helpText('Number of generated invite codes not yet redeemed')
            ->value(fn () => InviteCode::whereNull('used_by')->count());

        Prometheus::addGauge('Queue failed jobs')
            ->name('queue_failed_jobs_total')
            ->helpText('Number of jobs in the failed_jobs table')
            ->value(fn () => DB::table('failed_jobs')->count());

        Prometheus::addGauge('Application info')
            ->name('app_info')
            ->label('version')
            ->helpText('Always 1; the running YAAMS release is carried in the version label')
            ->value(fn () => [[1, [(string) config('app.version')]]]);

        Prometheus::addGauge('Users active')
            ->name('users_active')
            ->label('window')
            ->helpText('Number of users seen (any authenticated request) within the given time window')
            ->value(fn () => collect(self::ACTIVE_USER_WINDOWS)
                ->map(fn (int $seconds, string $window) => [
                    User::where('last_online_at', '>=', now()->subSeconds($seconds))->count(),
                    [$window],
                ])
                ->values()
                ->all());

        Prometheus::addGauge('Users with two-factor authentication')
            ->name('users_two_factor_enabled_total')
            ->helpText('Number of users with confirmed two-factor authentication')
            ->value(fn () => User::whereNotNull('two_factor_confirmed_at')->count());

        Prometheus::addGauge('Airline memberships total')
            ->name('airline_memberships_total')
            ->label('role')
            ->helpText('Number of airline memberships by per-airline role')
            ->value(function () {
                $counts = AirlineMembership::query()
                    ->toBase()
                    ->selectRaw('role, count(*) as aggregate')
                    ->groupBy('role')
                    ->pluck('aggregate', 'role');

                return collect(self::MEMBERSHIP_ROLES)
                    ->map(fn (string $role) => [(int) ($counts[$role] ?? 0), [$role]])
                    ->all();
            });

        Prometheus::addGauge('Flight block time accepted')
            ->name('flight_block_time_accepted_seconds')
            ->helpText('Total block time (block off to block on) of all accepted flights, in seconds')
            ->value(fn () => (int) Flight::query()
                ->toBase()
                ->where('status_id', 2)
                ->selectRaw(self::blockSecondsSql() . ' as aggregate')
                ->value('aggregate'));

        Prometheus::addGauge('Aircraft images total')
            ->name('aircraft_images_total')
            ->label('status')
            ->helpText('Number of aircraft screenshots by moderation status')
            ->value(function () {
                $counts = AircraftImage::query()
                    ->toBase()
                    ->selectRaw('status, count(*) as aggregate')
                    ->groupBy('status')
                    ->pluck('aggregate', 'status');

                return collect(self::AIRCRAFT_IMAGE_STATUSES)
                    ->map(fn (string $status) => [(int) ($counts[$status] ?? 0), [$status]])
                    ->all();
            });

        Prometheus::addGauge('NOTAMs active')
            ->name('notams_active_total')
            ->helpText('Number of NOTAMs that have not expired')
            ->value(fn () => Notam::active()->count());

        Prometheus::addGauge('Activity log entries total')
            ->name('activity_log_entries_total')
            ->label('level')
            ->helpText('Number of rows in the activity log by level (pruned by activitylog:clean)')
            ->value(function () {
                $counts = Activity::query()
                    ->toBase()
                    ->selectRaw('level, count(*) as aggregate')
                    ->groupBy('level')
                    ->pluck('aggregate', 'level');

                return collect(ActivityLevel::LEVELS)
                    ->keys()
                    ->map(fn (string $level) => [(int) ($counts[$level] ?? 0), [$level]])
                    ->all();
            });

        Prometheus::addGauge('Failed logins last 24h')
            ->name('login_failures_24h')
            ->helpText('Number of failed login attempts recorded in the activity log within the last 24 hours')
            ->value(fn () => Activity::query()
                ->where('level', ActivityLevel::WARNING)
                ->where('event', 'login_failed')
                ->where('created_at', '>=', now()->subDay())
                ->count());

        Prometheus::registerCollectorClasses([
            QueueSizeCollector::class,
            QueueOldestPendingJobCollector::class,
        ]);
    }

    /**
     * Sum of (blockon - blockoff) in seconds. There is no portable SQL for a
     * datetime difference, so this branches on the driver (SQLite runs the tests).
     */
    private static function blockSecondsSql(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => 'coalesce(sum((julianday(blockon) - julianday(blockoff)) * 86400), 0)',
            'pgsql' => 'coalesce(sum(extract(epoch from (blockon - blockoff))), 0)',
            default => 'coalesce(sum(timestampdiff(second, blockoff, blockon)), 0)',
        };
    }
}
