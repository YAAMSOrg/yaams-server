<?php

namespace App\Actions\Airlines;

use App\Models\AircraftImage;
use App\Models\Airline;
use App\Models\User;
use App\Support\ActivityLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Permanently deletes an airline with its flights, fleet, screenshots and
 * memberships. Used by the admin airline page.
 */
class DeleteAirline
{
    public function __invoke(Airline $airline, User $causer): void
    {
        $aircraftIds = $airline->aircraft()->pluck('id');
        $imagePaths = AircraftImage::whereIn('aircraft_id', $aircraftIds)->pluck('path');

        // Record the deletion before the airline row disappears; capture identity
        // in the properties so the audit entry stays meaningful.
        activity()
            ->causedBy($causer)
            ->withProperties([
                'level'    => ActivityLevel::WARNING,
                'name'     => $airline->name,
                'icao'     => $airline->icao_callsign,
                'members'  => $airline->users()->count(),
                'flights'  => $airline->flights()->count(),
                'aircraft' => $aircraftIds->count(),
            ])
            ->event('airline_deleted_by_admin')
            ->log('Deleted airline '.$airline->name.' ('.$airline->icao_callsign.')');

        DB::transaction(function () use ($airline, $aircraftIds) {
            // These FKs don't cascade, so delete in dependency order. Aircraft
            // images, invite codes and NOTAMs cascade with their parents.
            $airline->flights()->delete();
            AircraftImage::whereIn('aircraft_id', $aircraftIds)->delete();
            $airline->aircraft()->delete();
            $airline->users()->detach();
            $airline->delete();
        });

        // Files go last, once the rows are gone for sure.
        Storage::disk('local')->delete($imagePaths->all());
    }
}
