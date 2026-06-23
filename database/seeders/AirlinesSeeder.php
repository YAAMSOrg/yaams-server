<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AirlinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $airlines = [
            ['name' => "First VA",       'prefix' => "FV", 'icao_callsign' => 'FVA', 'atc_callsign' => 'FIRST',        'unit_is_lbs' => false, 'hub' => 'EDDF', 'country' => 'DE'],
            ['name' => "Second VA",      'prefix' => "SV", 'icao_callsign' => 'SVA', 'atc_callsign' => 'SECOND',       'unit_is_lbs' => true,  'hub' => 'KLAX', 'country' => 'US'],
            ['name' => "Third VA",       'prefix' => "TV", 'icao_callsign' => 'TVA', 'atc_callsign' => 'THIRD',        'unit_is_lbs' => true,  'hub' => 'EGLL', 'country' => 'GB'],
            ['name' => "Global Cargo",   'prefix' => "GC", 'icao_callsign' => 'GCG', 'atc_callsign' => 'GLOBAL CARGO', 'unit_is_lbs' => false, 'hub' => 'KJFK', 'country' => 'US'],
            ['name' => "Ocean Link",     'prefix' => "OL", 'icao_callsign' => 'OLN', 'atc_callsign' => 'OCEAN LINK',  'unit_is_lbs' => false, 'hub' => 'RJTT', 'country' => 'JP'],
            ['name' => "Euro Express",   'prefix' => "EE", 'icao_callsign' => 'EEX', 'atc_callsign' => 'EURO EXPRESS', 'unit_is_lbs' => false, 'hub' => 'LFPG', 'country' => 'FR'],
            ['name' => "Alpine Connect", 'prefix' => "AC", 'icao_callsign' => 'ALP', 'atc_callsign' => 'ALPINE',       'unit_is_lbs' => false, 'hub' => 'LSZH', 'country' => 'CH'],
            ['name' => "Pacific Airways",'prefix' => "PA", 'icao_callsign' => 'PAC', 'atc_callsign' => 'PACIFIC',      'unit_is_lbs' => true,  'hub' => 'KSFO', 'country' => 'US'],
            ['name' => "Apex Charter",   'prefix' => "AX", 'icao_callsign' => 'APX', 'atc_callsign' => 'APEX',         'unit_is_lbs' => true,  'hub' => 'LEMD', 'country' => 'ES'],
            ['name' => "Northern Air",   'prefix' => "NA", 'icao_callsign' => 'NTR', 'atc_callsign' => 'NORTHERN',     'unit_is_lbs' => false, 'hub' => 'ENGM', 'country' => 'NO'],
        ];

        foreach ($airlines as $airline) {
            DB::table('airlines')->insert($airline + [
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]);
        }
    }
}
