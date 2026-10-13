<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The web forms stored ICAO codes as typed. MySQL matched lowercase codes
        // case-insensitively, but Eloquent pairs eager-loaded airports
        // case-sensitively, so those relations loaded as null (issue #101).
        DB::table('flights')->update([
            'departure_icao' => DB::raw('UPPER(departure_icao)'),
            'arrival_icao' => DB::raw('UPPER(arrival_icao)'),
        ]);

        DB::table('aircraft')
            ->whereNotNull('current_loc')
            ->update(['current_loc' => DB::raw('UPPER(current_loc)')]);
    }

    public function down(): void
    {
        // Irreversible data normalization; uppercase codes are valid either way.
    }
};
