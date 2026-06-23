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
        $now = Carbon::now()->toDateTimeString();

        // Max Mustermann: Dispatcher on airline 1, Manager on airline 2
        DB::table('airline_memberships')->insert(['airline_id' => 1, 'user_id' => 2, 'role' => 'Dispatcher', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('airline_memberships')->insert(['airline_id' => 2, 'user_id' => 2, 'role' => 'Manager',    'created_at' => $now, 'updated_at' => $now]);

        // Homer Simpson: Pilot on airline 3
        DB::table('airline_memberships')->insert(['airline_id' => 3, 'user_id' => 1, 'role' => 'Pilot',     'created_at' => $now, 'updated_at' => $now]);

        // Admin: Manager on all three demo airlines (Super-Admin bypasses anyway)
        DB::table('airline_memberships')->insert(['airline_id' => 1, 'user_id' => 3, 'role' => 'Manager',   'created_at' => $now, 'updated_at' => $now]);
        DB::table('airline_memberships')->insert(['airline_id' => 2, 'user_id' => 3, 'role' => 'Manager',   'created_at' => $now, 'updated_at' => $now]);
        DB::table('airline_memberships')->insert(['airline_id' => 3, 'user_id' => 3, 'role' => 'Manager',   'created_at' => $now, 'updated_at' => $now]);

        // Randomly assign existing users (Homer: 1, Max: 2, Admin: 3) to new airlines (4 to 10)
        for ($airlineId = 4; $airlineId <= 10; $airlineId++) {
            foreach ([1, 2, 3] as $userId) {
                // ~60% probability of membership
                if (rand(0, 100) < 60) {
                    DB::table('airline_memberships')->insert([
                        'airline_id' => $airlineId,
                        'user_id' => $userId,
                        'role' => 'Pilot',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
