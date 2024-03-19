<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => "Max Mustermann",
            'email' => "test@test.com",
            'password' => Hash::make('start'),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'homebase' => "KJFK"
        ]);
        DB::table('users')->insert([
            'name' => "Homer Simpson",
            'email' => "homer@test.com",
            'password' => Hash::make('start'),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'homebase' => "EDDL"
        ]);
    }
}
