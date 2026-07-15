<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ActivityLevel;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Instance settings form — edits the key/value rows in the `settings`
     * table. Access is restricted to Super-Admins via the `role:Super-Admin`
     * middleware on the route group (see routes/web.php).
     */
    public function edit()
    {
        $settings = [
            'app_name'                    => Setting::get('app_name'),
            'timezone'                    => Setting::get('timezone'),
            'allow_user_airline_creation' => Setting::get('allow_user_airline_creation'),
            'allow_registration'          => Setting::get('allow_registration'),
            'show_public_stats'           => Setting::get('show_public_stats'),
            'support_email'               => Setting::get('support_email'),
            'LOG_LEVEL'                   => Setting::get('LOG_LEVEL'),
            'aircraft_image_max_filesize_kb'  => Setting::get('aircraft_image_max_filesize_kb'),
            'aircraft_image_max_dimension'    => Setting::get('aircraft_image_max_dimension'),
            'aircraft_image_max_per_aircraft' => Setting::get('aircraft_image_max_per_aircraft'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name'                    => 'required|string|max:255',
            'timezone'                    => 'required|timezone',
            'allow_user_airline_creation' => 'required|boolean',
            'allow_registration'          => 'required|boolean',
            'show_public_stats'           => 'required|boolean',
            'support_email'               => 'nullable|email|max:255',
            'LOG_LEVEL'                   => 'required|in:debug,info,warning',
            'aircraft_image_max_filesize_kb'  => 'required|integer|min:256|max:51200',
            'aircraft_image_max_dimension'    => 'required|integer|min:320|max:10000',
            'aircraft_image_max_per_aircraft' => 'required|integer|min:1|max:100',
        ]);

        Setting::set('app_name', $validated['app_name']);
        Setting::set('timezone', $validated['timezone']);
        Setting::set('allow_user_airline_creation', $request->boolean('allow_user_airline_creation') ? '1' : '0');
        Setting::set('allow_registration', $request->boolean('allow_registration') ? '1' : '0');
        Setting::set('show_public_stats', $request->boolean('show_public_stats') ? '1' : '0');
        Setting::set('support_email', $validated['support_email'] ?? null);
        Setting::set('LOG_LEVEL', $validated['LOG_LEVEL']);
        Setting::set('aircraft_image_max_filesize_kb', (string) $validated['aircraft_image_max_filesize_kb']);
        Setting::set('aircraft_image_max_dimension', (string) $validated['aircraft_image_max_dimension']);
        Setting::set('aircraft_image_max_per_aircraft', (string) $validated['aircraft_image_max_per_aircraft']);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('settings_updated')
            ->log('Updated instance settings');

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Instance settings saved.');
    }
}
