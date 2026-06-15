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
            'id' => 1,
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
            'status_id' => '1',
            'remarks' => 'Example remark',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('flights')->insert([
            'id' => 2,
            'airline_id' => "1",
            'callsign' => "1338",
            'flightnumber' => "3447",
            'departure_icao' => "EDDS",
            'arrival_icao' => 'EDDK',
            'aircraft_id' => '1',
            'crzalt' => '23000',
            'blockoff' => '2024-04-10 11:21:26.000000',
            'blockon' => '2024-04-10 12:45:35.000000',
            'burned_fuel' => '3841',
            'route' => 'DCT',
            'online_network_id' => '2',
            'pilot_id' => '2',
            'status_id' => '2',
            'remarks' => 'Test Flight',
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        $memberships = DB::table('airline_memberships')->get();
        if ($memberships->isEmpty()) {
            return;
        }

        $airports = DB::table('airports')
            ->whereIn('icao_code', ['EDDF', 'EDDM', 'EGLL', 'LFPG', 'EHAM', 'KJFK', 'KLAX', 'LOWW', 'EFHK', 'KMIA', 'EDDK', 'EDDS', 'KORD', 'KSFO'])
            ->pluck('icao_code')
            ->toArray();
        if (empty($airports)) {
            $airports = ['EDDF', 'EDDM', 'KJFK', 'EGLL'];
        }

        $routes = [
            'DCT',
            'KUMIK Y854 BOMBI T721 SUNEG T715 KRH T128 BADSO',
            'N858 UZ729 DEKET UT180 VESAN',
            'UT180 VALDI UM736 LUKIP UN858 LASGA',
            'UM736 OMAKO UN858 KELGO',
            'UL608 TEDGO UM736 EEL',
        ];

        // Target: ~1100 total flights
        $flightsPerMembership = (int)ceil(1100 / $memberships->count());

        foreach ($memberships as $membership) {
            $pilotId = $membership->user_id;
            $airlineId = $membership->airline_id;

            $aircraftList = DB::table('aircraft')->where('used_by', $airlineId)->get();
            if ($aircraftList->isEmpty()) {
                continue;
            }

            $date = Carbon::now()->subDays(730);

            for ($i = 0; $i < $flightsPerMembership; $i++) {
                $aircraft = $aircraftList->random();
                
                $date->addDays(rand(2, 6))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                
                $durationMinutes = rand(45, 480);
                $blockoff = $date->toDateTimeString();
                
                $blockonDate = $date->copy()->addMinutes($durationMinutes);
                $blockon = $blockonDate->toDateTimeString();

                $isLightAircraft = in_array($aircraft->model, ['C172', 'SR22']);
                $crzalt = $isLightAircraft ? rand(3000, 10000) : (rand(18, 41) * 1000);

                $burnedFuel = $durationMinutes * ($isLightAircraft ? rand(1, 2) : rand(40, 100));

                $randVal = rand(0, 100);
                if ($randVal < 85) {
                    $statusId = 2;
                } elseif ($randVal < 95) {
                    $statusId = 1;
                } else {
                    $statusId = 3;
                }

                $dep = $airports[array_rand($airports)];
                $arr = $airports[array_rand($airports)];
                while ($dep === $arr) {
                    $arr = $airports[array_rand($airports)];
                }

                $callsign = (string)rand(100, 9999);
                $flightnumber = rand(100, 9999);

                DB::table('flights')->insert([
                    'airline_id' => $airlineId,
                    'callsign' => $callsign,
                    'flightnumber' => $flightnumber,
                    'departure_icao' => $dep,
                    'arrival_icao' => $arr,
                    'aircraft_id' => $aircraft->id,
                    'crzalt' => $crzalt,
                    'blockoff' => $blockoff,
                    'blockon' => $blockon,
                    'burned_fuel' => $burnedFuel,
                    'route' => $routes[array_rand($routes)],
                    'online_network_id' => rand(1, 4),
                    'pilot_id' => $pilotId,
                    'status_id' => $statusId,
                    'remarks' => 'Generated flight log',
                    'created_at' => $date->toDateTimeString(),
                    'updated_at' => $date->toDateTimeString(),
                ]);
            }
        }
    }
}
