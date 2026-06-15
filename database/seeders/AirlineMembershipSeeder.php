<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AirlineMembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Max Mustermann is member of the two demo airlines
        DB::table('airline_memberships')->insert([
            'airline_id' => "2",
            'user_id' => "2",
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('airline_memberships')->insert([
            'airline_id' => "1",
            'user_id' => "2",
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('airline_memberships')->insert([
            'airline_id' => "3",
            'user_id' => "1",
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('airline_memberships')->insert([
            'airline_id' => "1",
            'user_id' => "3",
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('airline_memberships')->insert([
            'airline_id' => "2",
            'user_id' => "3",
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
        DB::table('airline_memberships')->insert([
            'airline_id' => "3",
            'user_id' => "3",
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);

        // Randomly assign existing users (Homer: 1, Max: 2, Admin: 3) to new airlines (4 to 10)
        for ($airlineId = 4; $airlineId <= 10; $airlineId++) {
            foreach ([1, 2, 3] as $userId) {
                // ~60% probability of membership
                if (rand(0, 100) < 60) {
                    DB::table('airline_memberships')->insert([
                        'airline_id' => $airlineId,
                        'user_id' => $userId,
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'updated_at' => Carbon::now()->toDateTimeString(),
                    ]);
                }
            }
        }
    }
}
