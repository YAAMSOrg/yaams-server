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
        DB::table('aircraft')->insert([
            'registration' => "D-EXAM",
            'manufacturer' => "Boeing",
            'model' => '737-800',
            'current_loc' => 'KJFK',
            'remarks' => "Special paint scheme",
            'used_by' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('aircraft')->insert([
            'registration' => "D-ABCD",
            'manufacturer' => "Boeing",
            'model' => '737-800',
            'current_loc' => 'EHAM',
            #'remarks' => "",
            'used_by' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);        
        DB::table('aircraft')->insert([
            'registration' => "D-ALPA",
            'manufacturer' => "Airbus",
            'model' => 'A320-200',
            'current_loc' => 'EDDF',
            #'remarks' => "Cool airplane",
            'used_by' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('aircraft')->insert([
            'registration' => "N1337H",
            'manufacturer' => "Cessna",
            'model' => 'C172',
            'current_loc' => 'EDKB',
            'remarks' => "For flight training",
            'used_by' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);                
        DB::table('aircraft')->insert([
            'registration' => "EI-JFK",
            'manufacturer' => "Airbus",
            'model' => 'A330-300',
            'current_loc' => 'EGLL',
            'remarks' => "Big bird",
            'used_by' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('aircraft')->insert([
            'registration' => "OE-ESU",
            'manufacturer' => "Airbus",
            'model' => 'A320-200',
            'current_loc' => 'LOWW',
            #'remarks' => "Cool airplane",
            'used_by' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('aircraft')->insert([
            'registration' => "D-TEST",
            'manufacturer' => "McDonell Douglas",
            'model' => 'MD-11',
            'current_loc' => 'LOWI',
            #'remarks' => "Cool airplane",
            'used_by' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
    }
}
