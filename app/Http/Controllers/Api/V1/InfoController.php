<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class InfoController extends Controller
{
    /**
     * Public instance metadata for API clients (e.g. ACARS "connect to your VA"
     * setup screens). Unauthenticated by design - exposes only what the public
     * landing page already shows.
     */
    public function index()
    {
        return response()->json([
            'data' => [
                'name' => Setting::get('app_name', config('app.name')),
                'version' => config('app.version'),
                'apiVersion' => 'v1',
                'supportEmail' => Setting::get('support_email'),
                'features' => [
                    'registration' => Setting::get('allow_registration', '1') === '1',
                    'userAirlineCreation' => Setting::get('allow_user_airline_creation') === '1',
                ],
            ],
        ]);
    }
}
