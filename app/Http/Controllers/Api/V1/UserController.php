<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\Request;

/**
 * @group Account
 *
 * The token owner's own account.
 */
class UserController extends Controller
{
    /**
     * Current user
     *
     * "Who am I" - returns the account the API token belongs to, with its
     * airline memberships. Useful as a token sanity check for API clients.
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Homer Simpson",
     *     "email": "homer@test.com",
     *     "airlines": [
     *       {
     *         "id": 1,
     *         "name": "Example Virtual Airlines",
     *         "prefix": "EV",
     *         "icaoCallsign": "EVA",
     *         "atcCallsign": "EXAMPLE",
     *         "unitIsLbs": false,
     *         "requirePirepReview": true,
     *         "locationContinuity": false,
     *         "createdAt": "2026-01-01T00:00:00.000000Z",
     *         "updatedAt": "2026-01-01T00:00:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     */
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load('airlines'));
    }
}
