<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AircraftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $originalAircraft = [
            [
                'registration' => "D-EXAM",
                'manufacturer' => "Boeing",
                'model' => '737-800',
                'current_loc' => 'KJFK',
                'remarks' => "Special paint scheme",
                'used_by' => 1,
            ],
            [
                'registration' => "D-ABCD",
                'manufacturer' => "Boeing",
                'model' => '737-800',
                'current_loc' => 'EHAM',
                'used_by' => 1,
            ],        
            [
                'registration' => "D-ALPA",
                'manufacturer' => "Airbus",
                'model' => 'A320-200',
                'current_loc' => 'EDDF',
                'used_by' => 1,
            ],
            [
                'registration' => "N1337H",
                'manufacturer' => "Cessna",
                'model' => 'C172',
                'current_loc' => 'EDKB',
                'remarks' => "For flight training",
                'used_by' => 1,
            ],                
            [
                'registration' => "EI-JFK",
                'manufacturer' => "Airbus",
                'model' => 'A330-300',
                'current_loc' => 'EGLL',
                'remarks' => "Big bird",
                'used_by' => 1,
            ],
            [
                'registration' => "OE-ESU",
                'manufacturer' => "Airbus",
                'model' => 'A320-200',
                'current_loc' => 'LOWW',
                'used_by' => 1,
            ],
            [
                'registration' => "D-TEST",
                'manufacturer' => "McDonell Douglas",
                'model' => 'MD-11',
                'current_loc' => 'LOWI',
                'used_by' => 1,
            ],
            [
                'registration' => "D-EXAM",
                'manufacturer' => "Boeing",
                'model' => '737-800',
                'current_loc' => 'EFHK',
                'remarks' => "Special paint scheme",
                'used_by' => 3,
            ],
            [
                'registration' => "N1338L",
                'manufacturer' => "Cirrus",
                'model' => 'SR22',
                'current_loc' => 'KMIA',
                'remarks' => "Special paint scheme",
                'used_by' => 2,
            ],
        ];

        foreach ($originalAircraft as $ac) {
            DB::table('aircraft')->insert($ac + [
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]);
        }

        $types = [
            ['manufacturer' => 'Boeing', 'model' => '737-800'],
            ['manufacturer' => 'Boeing', 'model' => '777-300ER'],
            ['manufacturer' => 'Boeing', 'model' => '787-9'],
            ['manufacturer' => 'Airbus', 'model' => 'A320neo'],
            ['manufacturer' => 'Airbus', 'model' => 'A321neo'],
            ['manufacturer' => 'Airbus', 'model' => 'A350-900'],
            ['manufacturer' => 'Bombardier', 'model' => 'CRJ-900'],
            ['manufacturer' => 'Embraer', 'model' => 'E195'],
        ];

        $airports = DB::table('airports')
            ->whereIn('icao_code', ['EDDF', 'EDDM', 'EGLL', 'LFPG', 'EHAM', 'KJFK', 'KLAX', 'LOWW', 'EFHK', 'KMIA', 'EDDK', 'EDDS'])
            ->pluck('icao_code')
            ->toArray();
        if (empty($airports)) {
            $airports = ['EDDF', 'EDDM', 'KJFK'];
        }

        $regSuffixes = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        for ($airlineId = 1; $airlineId <= 10; $airlineId++) {
            // Seed 4 extra aircraft for each airline
            for ($i = 0; $i < 4; $i++) {
                $type = $types[array_rand($types)];
                $airport = $airports[array_rand($airports)];
                
                $countryPrefix = $airlineId % 2 === 0 ? 'N' : 'D-';
                if ($countryPrefix === 'N') {
                    $reg = 'N' . rand(100, 999) . $regSuffixes[rand(0, 25)] . $regSuffixes[rand(0, 25)];
                } else {
                    $reg = 'D-A' . $regSuffixes[rand(0, 25)] . $regSuffixes[rand(0, 25)] . $regSuffixes[rand(0, 25)];
                }

                if (DB::table('aircraft')->where('registration', $reg)->where('used_by', $airlineId)->exists()) {
                    continue;
                }

                DB::table('aircraft')->insert([
                    'registration' => $reg,
                    'manufacturer' => $type['manufacturer'],
                    'model' => $type['model'],
                    'current_loc' => $airport,
                    'remarks' => "Automatically generated airframe",
                    'used_by' => $airlineId,
                    'created_at' => Carbon::now()->toDateTimeString(),
                    'updated_at' => Carbon::now()->toDateTimeString(),
                ]);
            }
        }
    }
}
