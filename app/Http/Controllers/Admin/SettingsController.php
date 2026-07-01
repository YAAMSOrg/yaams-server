<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
            'allow_user_airline_creation' => Setting::get('allow_user_airline_creation'),
            'allow_registration'          => Setting::get('allow_registration'),
            'support_email'               => Setting::get('support_email'),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name'                    => 'required|string|max:255',
            'allow_user_airline_creation' => 'required|boolean',
            'allow_registration'          => 'required|boolean',
            'support_email'               => 'nullable|email|max:255',
        ]);

        Setting::set('app_name', $validated['app_name']);
        Setting::set('allow_user_airline_creation', $request->boolean('allow_user_airline_creation') ? '1' : '0');
        Setting::set('allow_registration', $request->boolean('allow_registration') ? '1' : '0');
        Setting::set('support_email', $validated['support_email'] ?? null);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Instance settings saved.');
    }
}
