<?php

namespace App\Actions\Users;

use App\Models\AircraftImage;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

/**
 * Collects the personal data YAAMS holds about a user, for the GDPR right of
 * access / data portability (Art. 15 and 20). Returns a plain array that is
 * serialized to JSON by SettingsController::export().
 *
 * Secrets are never included: no password hash, 2FA secret, recovery codes,
 * remember token or API token hashes.
 */
class ExportUserData
{
    public function __invoke(User $user): array
    {
        $user->loadMissing(['airlines', 'ownedAirlines', 'tokens']);

        return [
            'exported_at' => now()->toIso8601String(),
            'instance'    => config('app.url'),

            'profile' => [
                'id'                  => $user->id,
                'name'                => $user->name,
                'email'               => $user->email,
                'email_verified_at'   => $user->email_verified_at?->toIso8601String(),
                'email_notifications' => (bool) $user->email_notifications,
                'two_factor_enabled'  => $user->hasTwoFactorEnabled(),
                'roles'               => $user->getRoleNames()->values()->all(),
                'registered_at'       => $user->created_at?->toIso8601String(),
                'last_online_at'      => $user->last_online_at?->toIso8601String(),
                'legal_accepted_at'   => $user->legal_accepted_at?->toIso8601String(),
            ],

            'airline_memberships' => $user->airlines->map(fn ($airline) => [
                'airline' => $airline->name,
                'icao'    => $airline->icao_callsign,
                'role'    => $airline->pivot->role,
                'owner'   => $airline->owner_user_id === $user->id,
            ])->values()->all(),

            'flights' => $user->flights()
                ->with(['airline', 'aircraft', 'status'])
                ->orderBy('blockoff')
                ->get()
                ->map(fn ($flight) => [
                    'id'                => $flight->id,
                    'airline'           => $flight->airline?->icao_callsign,
                    'flight_number'     => $flight->full_flight_number,
                    'callsign'          => $flight->callsign,
                    'departure'         => $flight->departure_icao,
                    'arrival'           => $flight->arrival_icao,
                    'aircraft'          => $flight->aircraft?->registration,
                    'cruise_altitude'   => $flight->crzalt,
                    'block_off'         => $flight->blockoff,
                    'block_on'          => $flight->blockon,
                    'burned_fuel'       => $flight->burned_fuel,
                    'route'             => $flight->route,
                    'remarks'           => $flight->remarks,
                    'status'            => $flight->status?->name,
                    'filed_at'          => $flight->created_at?->toIso8601String(),
                ])->all(),

            'aircraft_screenshots' => AircraftImage::query()
                ->where('uploaded_by', $user->id)
                ->with('aircraft')
                ->get()
                ->map(fn ($image) => [
                    'aircraft'    => $image->aircraft?->registration,
                    'status'      => $image->status,
                    'uploaded_at' => $image->created_at?->toIso8601String(),
                ])->all(),

            'notifications' => $user->notifications()
                ->get()
                ->map(fn ($notification) => [
                    'title'      => $notification->data['title'] ?? null,
                    'message'    => $notification->data['message'] ?? null,
                    'read_at'    => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->toIso8601String(),
                ])->all(),

            'api_tokens' => $user->tokens->map(fn ($token) => [
                'name'         => $token->name,
                'abilities'    => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'created_at'   => $token->created_at?->toIso8601String(),
            ])->values()->all(),

            // Actions the user performed, and actions on their account. Who
            // performed an action on the account is left out: that is another
            // person's data.
            'activity_log' => Activity::query()
                ->where(fn ($q) => $q
                    ->where(fn ($q) => $q->where('causer_type', $user->getMorphClass())->where('causer_id', $user->id))
                    ->orWhere(fn ($q) => $q->where('subject_type', $user->getMorphClass())->where('subject_id', $user->id)))
                ->orderBy('id')
                ->get()
                ->map(fn ($activity) => [
                    'event'       => $activity->event,
                    'description' => $activity->description,
                    'level'       => $activity->level,
                    'created_at'  => $activity->created_at?->toIso8601String(),
                ])->all(),
        ];
    }
}
