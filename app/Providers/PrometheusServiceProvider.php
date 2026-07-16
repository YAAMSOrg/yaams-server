<?php

namespace App\Providers;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\InviteCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
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

        Prometheus::registerCollectorClasses([
            QueueSizeCollector::class,
        ]);
    }
}
