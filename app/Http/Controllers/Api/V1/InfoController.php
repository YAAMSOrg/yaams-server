<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;

/**
 * @group Instance
 *
 * Public metadata about this YAAMS instance.
 */
class InfoController extends Controller
{
    /**
     * Instance info
     *
     * Public instance metadata for API clients (e.g. ACARS "connect to your VA"
     * setup screens). Unauthenticated by design - exposes only what the public
     * landing page already shows.
     *
     * @unauthenticated
     *
     * @response 200 {
     *   "data": {
     *     "name": "Example Virtual Airlines",
     *     "version": "1.1.0",
     *     "apiVersion": "v1",
     *     "supportEmail": "ops@example.com",
     *     "features": {
     *       "registration": true,
     *       "userAirlineCreation": false
     *     }
     *   }
     * }
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
