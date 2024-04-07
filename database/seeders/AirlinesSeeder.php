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
        DB::table('airlines')->insert([
            'name' => "First VA",
            'prefix' => "FV",
            'icao_callsign' => 'FVA',
            'atc_callsign' => 'FIRST',
            'unit_is_lbs' => false,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);
        DB::table('airlines')->insert([
            'name' => "Second VA",
            'prefix' => "SV",
            'icao_callsign' => 'SVA',
            'atc_callsign' => 'SECOND',
            'unit_is_lbs' => true,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);
        DB::table('airlines')->insert([
            'name' => "Third VA",
            'prefix' => "TV",
            'icao_callsign' => 'TVA',
            'atc_callsign' => 'THIRD',
            'unit_is_lbs' => true,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);
    }
}
