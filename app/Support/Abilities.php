<?php

namespace App\Support;

use App\Models\Airline;
use App\Models\User;

/**
 * The `abilities` the API reports so clients can build role-aware UIs. Every
 * flag is computed through the same Gate/policy checks the endpoints enforce
 * (App\Policies\AirlinePolicy), never from a role mapping of its own. They are
 * UI hints only - the server still enforces every permission.
 */
class Abilities
{
    /**
     * Per-airline abilities, each an AirlinePolicy method.
     */
    public const AIRLINE = [
        'fileFlights',
        'reviewFlights',
        'addAircraft',
        'editAircraft',
        'manageMembers',
        'manageInviteCodes',
        'manageNotams',
        'editSettings',
    ];

    /**
     * Every per-airline ability as an explicit boolean. All false while the
     * airline is suspended: the `airline.writable` middleware then refuses
     * every write, Super-Admin included.
     *
     * @return array<string, bool>
     */
    public static function forAirline(User $user, Airline $airline): array
    {
        $writable = ! $airline->isSuspended();

        return collect(self::AIRLINE)
            ->mapWithKeys(fn (string $ability) => [$ability => $writable && $user->can($ability, $airline)])
            ->all();
    }

    /**
     * Instance-level abilities.
     *
     * @return array<string, bool>
     */
    public static function forInstance(User $user): array
    {
        return [
            'foundAirline' => $user->can('create', Airline::class),
            'administerInstance' => $user->can('administerInstance'),
        ];
    }
}
