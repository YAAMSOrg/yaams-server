<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SetupController extends Controller
{
    public function show()
    {
        if (User::count() > 0) {
            return redirect()->route('dashboard');
        }

        return view('setup.index');
    }

    public function store(Request $request)
    {
        if (User::count() > 0) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'app_name'         => 'required|string|max:255',
            'airline_name'     => 'required|string|max:255',
            'airline_prefix'   => 'required|string|max:2|min:2|alpha',
            'airline_icao'     => 'required|string|max:3|min:2|alpha',
            'airline_callsign' => 'required|alpha|min:2|max:10',
            'airline_hub'      => 'required|string|max:4|exists:airports,icao_code',
            'airline_country'  => 'required|string|size:2|alpha',
            'airline_desc'     => 'nullable|string|max:1000',
            'airline_website'  => 'nullable|url|max:255',
            'airline_founded'  => 'nullable|date',
            'unit_is_lbs'      => 'nullable|boolean',
            'admin_name'       => 'required|string|max:255',
            'admin_email'      => 'required|email|max:255',
            'admin_password'   => 'required|confirmed|min:8',
        ]);

        // Import airports outside the main transaction — large bulk insert
        DB::unprepared(file_get_contents(base_path('resources/db/airports.sql')));

        DB::transaction(function () use ($request) {
            // Roles & permissions
            $pilotRole      = Role::firstOrCreate(['name' => 'Pilot',       'guard_name' => 'web']);
            $managerRole    = Role::firstOrCreate(['name' => 'Manager',     'guard_name' => 'web']);
            $superAdminRole = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);

            $addAircraft  = Permission::firstOrCreate(['name' => 'add aircraft',  'guard_name' => 'web']);
            $editAircraft = Permission::firstOrCreate(['name' => 'edit aircraft', 'guard_name' => 'web']);
            $reviewFlight = Permission::firstOrCreate(['name' => 'review flight', 'guard_name' => 'web']);

            $managerRole->syncPermissions([$addAircraft, $editAircraft, $reviewFlight]);

            // Flight statuses (normally seeded; create them here for fresh installs)
            $now = Carbon::now()->toDateTimeString();
            DB::table('flight_statuses')->insertOrIgnore([
                ['id' => 1, 'name' => 'New',      'created_at' => $now, 'updated_at' => $now],
                ['id' => 2, 'name' => 'Accepted', 'created_at' => $now, 'updated_at' => $now],
                ['id' => 3, 'name' => 'Rejected', 'created_at' => $now, 'updated_at' => $now],
                ['id' => 4, 'name' => 'Enroute',  'created_at' => $now, 'updated_at' => $now],
            ]);

            // Online networks
            DB::table('online_networks')->insertOrIgnore([
                ['networkname' => 'Offline',    'created_at' => $now, 'updated_at' => $now],
                ['networkname' => 'VATSIM',     'created_at' => $now, 'updated_at' => $now],
                ['networkname' => 'IVAO',       'created_at' => $now, 'updated_at' => $now],
                ['networkname' => 'PilotEdge',  'created_at' => $now, 'updated_at' => $now],
            ]);

            // Instance name
            DB::table('settings')->upsert(
                ['key' => 'app_name', 'value' => $request->app_name, 'created_at' => $now, 'updated_at' => $now],
                ['key'],
                ['value', 'updated_at']
            );

            // Airline
            $icao     = strtoupper($request->airline_icao);
            $callsign = strtoupper($request->airline_callsign);

            $airline = Airline::create([
                'name'                 => $request->airline_name,
                'prefix'               => strtoupper($request->airline_prefix),
                'icao_callsign'        => $icao,
                'atc_callsign'         => $callsign,
                'unit_is_lbs'          => $request->boolean('unit_is_lbs'),
                'hub'                  => strtoupper($request->airline_hub),
                'country'              => strtoupper($request->airline_country),
                'description'          => $request->airline_desc,
                'website'              => $request->airline_website,
                'founded_at'           => $request->airline_founded ?? now()->toDateString(),
                'active'               => true,
                'require_pirep_review' => true,
            ]);

            // Admin user
            $user = User::create([
                'name'              => $request->admin_name,
                'email'             => $request->admin_email,
                'password'          => Hash::make($request->admin_password),
                'email_verified_at' => $now,
            ]);

            $user->assignRole($superAdminRole);
            $airline->users()->attach($user, ['role' => 'Manager']);

            auth()->login($user);
            session(['activeairline' => $airline]);
        });

        return redirect()->route('dashboard')->with('setup_complete', true);
    }
}
