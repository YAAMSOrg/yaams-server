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
                'engine_type' => 'CFM56-7B26',
                'satcom' => false,
                'winglets' => true,
                'selcal' => 'AB-CD',
                'hex_code' => '3C66A4',
                'msn' => '29314',
                'mtow' => 79010,
                'mzfw' => 62730,
                'mlw' => 66349,
                'current_loc' => 'KJFK',
                'remarks' => "Special paint scheme",
                'used_by' => 1,
            ],
            [
                'registration' => "D-ABCD",
                'manufacturer' => "Boeing",
                'model' => '737-800',
                'engine_type' => 'CFM56-7B26',
                'satcom' => true,
                'winglets' => true,
                'selcal' => 'EF-GH',
                'hex_code' => '3C66A5',
                'msn' => '29315',
                'mtow' => 79010,
                'mzfw' => 62730,
                'mlw' => 66349,
                'current_loc' => 'EHAM',
                'used_by' => 1,
            ],        
            [
                'registration' => "D-ALPA",
                'manufacturer' => "Airbus",
                'model' => 'A320-200',
                'engine_type' => 'IAE V2527-A5',
                'satcom' => true,
                'winglets' => true,
                'selcal' => 'HR-BL',
                'hex_code' => '3C4D05',
                'msn' => '1422',
                'mtow' => 78000,
                'mzfw' => 62500,
                'mlw' => 66000,
                'current_loc' => 'EDDF',
                'used_by' => 1,
            ],
            [
                'registration' => "N1337H",
                'manufacturer' => "Cessna",
                'model' => 'C172',
                'engine_type' => 'Lycoming IO-360-L2A',
                'satcom' => false,
                'winglets' => false,
                'selcal' => null,
                'hex_code' => 'A08F56',
                'msn' => '1721345',
                'mtow' => 1157,
                'mzfw' => 1100,
                'mlw' => 1157,
                'current_loc' => 'EDKB',
                'remarks' => "For flight training",
                'used_by' => 1,
            ],                
            [
                'registration' => "EI-JFK",
                'manufacturer' => "Airbus",
                'model' => 'A330-300',
                'engine_type' => 'RR Trent 772B-60',
                'satcom' => true,
                'winglets' => true,
                'selcal' => 'CS-LN',
                'hex_code' => '400A2C',
                'msn' => '1154',
                'mtow' => 233000,
                'mzfw' => 175000,
                'mlw' => 187000,
                'current_loc' => 'EGLL',
                'remarks' => "Big bird",
                'used_by' => 1,
            ],
            [
                'registration' => "OE-ESU",
                'manufacturer' => "Airbus",
                'model' => 'A320-200',
                'engine_type' => 'CFM56-5B4',
                'satcom' => false,
                'winglets' => true,
                'selcal' => 'JK-LM',
                'hex_code' => '44012A',
                'msn' => '2541',
                'mtow' => 77000,
                'mzfw' => 61000,
                'mlw' => 64500,
                'current_loc' => 'LOWW',
                'used_by' => 1,
            ],
            [
                'registration' => "D-TEST",
                'manufacturer' => "McDonell Douglas",
                'model' => 'MD-11',
                'engine_type' => 'GE CF6-80C2D1F',
                'satcom' => true,
                'winglets' => true,
                'selcal' => 'NP-QR',
                'hex_code' => '3C910B',
                'msn' => '48443',
                'mtow' => 286000,
                'mzfw' => 188000,
                'mlw' => 200000,
                'current_loc' => 'LOWI',
                'used_by' => 1,
            ],
            [
                'registration' => "D-EXAM",
                'manufacturer' => "Boeing",
                'model' => '737-800',
                'engine_type' => 'CFM56-7B26',
                'satcom' => false,
                'winglets' => true,
                'current_loc' => 'EFHK',
                'remarks' => "Special paint scheme",
                'used_by' => 3,
            ],
            [
                'registration' => "N1338L",
                'manufacturer' => "Cirrus",
                'model' => 'SR22',
                'engine_type' => 'Continental IO-550-N',
                'satcom' => false,
                'winglets' => false,
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

        $engineMapping = [
            '737-800' => 'CFM56-7B26',
            '777-300ER' => 'GE90-115B',
            '787-9' => 'GEnx-1B',
            'A320neo' => 'LEAP-1A26',
            'A321neo' => 'LEAP-1A32',
            'A350-900' => 'RR Trent XWB-84',
            'CRJ-900' => 'GE CF34-8C5',
            'E195' => 'GE CF34-10E',
        ];

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

                $engine = $engineMapping[$type['model']] ?? 'Unknown';

                DB::table('aircraft')->insert([
                    'registration' => $reg,
                    'manufacturer' => $type['manufacturer'],
                    'model' => $type['model'],
                    'engine_type' => $engine,
                    'satcom' => rand(0, 1) === 1,
                    'winglets' => rand(0, 1) === 1,
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
