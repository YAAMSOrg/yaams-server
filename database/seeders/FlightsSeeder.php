<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class FlightsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('flights')->insert([
            'airline_id' => "1",
            'callsign' => "1337",
            'flightnumber' => "3446",
            'departure_icao' => "EDDK",
            'arrival_icao' => 'EDDS',
            'aircraft_id' => '1',
            'crzalt' => '21000',
            'blockoff' => '2024-04-09 06:24:26.000000',
            'blockon' => '2024-04-09 07:15:35.000000',
            'burned_fuel' => '3185',
            'route' => 'KUMIK Y854 BOMBI T721 SUNEG T715 KRH T128 BADSO',
            'online_network_id' => '3',
            'pilot_id' => '2',
            'remarks' => 'Example remark',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
    }
}
