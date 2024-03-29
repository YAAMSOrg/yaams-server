<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class AirportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Eloquent::unguard();
        $path = getcwd() . '/resources/db/airports.sql';
        DB::unprepared(file_get_contents($path));
       // $this->command->info('Airports table seeded!');
    }

    
}
