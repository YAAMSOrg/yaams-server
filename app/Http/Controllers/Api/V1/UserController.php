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
     * airline memberships and what it may do. Useful as a token sanity check
     * and to build role-aware UIs.
     *
     * **Abilities are UI hints.** Gate UI on the `abilities` flags, never on
     * role names - roles may be renamed or added. The server still enforces
     * every permission, so clients must keep handling 403. Every key is always
     * sent as an explicit boolean; treat a missing or unknown key as `false`,
     * so new abilities can be added without a version bump. Super-Admins get
     * their effective abilities (all `true`), and every per-airline ability is
     * `false` while the airline is suspended (it is read-only).
     *
     * Instance-level `abilities`:
     * - `foundAirline` - `POST /api/v1/airlines` (Super-Admin, or anyone when the instance allows it)
     * - `administerInstance` - the web admin area `/admin` (Super-Admin)
     *
     * Per-airline `airlines[].membership` (also on `GET /api/v1/airlines` and
     * `GET /api/v1/airlines/{airline}`, `null` for a non-member):
     * - `role` - display only, one of `pilot`, `dispatcher`, `manager`
     * - `isOwner` - the airline's owner (always has Manager rights)
     * - `abilities.fileFlights` - `POST /api/v1/airlines/{airline}/flights`
     * - `abilities.reviewFlights` - `GET /api/v1/airlines/{airline}/flights/review`, `POST /api/v1/flights/{flight}/accept` and `/reject` (only Managers may review their own PIREPs)
     * - `abilities.addAircraft` - `POST /api/v1/airlines/{airline}/aircraft`
     * - `abilities.editAircraft` - edit and retire aircraft, moderate screenshots (web UI)
     * - `abilities.manageMembers` - change member roles, remove members (web UI)
     * - `abilities.manageInviteCodes` - create and delete invite codes (web UI)
     * - `abilities.manageNotams` - airline announcements (web UI)
     * - `abilities.editSettings` - airline operations settings (web UI)
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Homer Simpson",
     *     "email": "homer@test.com",
     *     "abilities": {
     *       "foundAirline": false,
     *       "administerInstance": false
     *     },
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
     *         "isSuspended": false,
     *         "membership": {
     *           "role": "manager",
     *           "isOwner": true,
     *           "abilities": {
     *           "fileFlights": true,
     *           "reviewFlights": true,
     *           "addAircraft": true,
     *           "editAircraft": true,
     *           "manageMembers": true,
     *           "manageInviteCodes": true,
     *           "manageNotams": true,
     *           "editSettings": true
     *           }
     *         },
     *         "createdAt": "2026-01-01T00:00:00.000000Z",
     *         "updatedAt": "2026-01-01T00:00:00.000000Z"
     *       },
     *       {
     *         "id": 2,
     *         "name": "Sample Air",
     *         "prefix": "SA",
     *         "icaoCallsign": "SAM",
     *         "atcCallsign": "SAMPLE",
     *         "unitIsLbs": false,
     *         "requirePirepReview": true,
     *         "locationContinuity": false,
     *         "isSuspended": false,
     *         "membership": {
     *           "role": "dispatcher",
     *           "isOwner": false,
     *           "abilities": {
     *           "fileFlights": true,
     *           "reviewFlights": true,
     *           "addAircraft": false,
     *           "editAircraft": false,
     *           "manageMembers": false,
     *           "manageInviteCodes": false,
     *           "manageNotams": false,
     *           "editSettings": false
     *           }
     *         },
     *         "createdAt": "2026-01-01T00:00:00.000000Z",
     *         "updatedAt": "2026-01-01T00:00:00.000000Z"
     *       },
     *       {
     *         "id": 3,
     *         "name": "Demo Wings",
     *         "prefix": "DW",
     *         "icaoCallsign": "DWG",
     *         "atcCallsign": "DEMO",
     *         "unitIsLbs": false,
     *         "requirePirepReview": true,
     *         "locationContinuity": false,
     *         "isSuspended": false,
     *         "membership": {
     *           "role": "pilot",
     *           "isOwner": false,
     *           "abilities": {
     *           "fileFlights": true,
     *           "reviewFlights": false,
     *           "addAircraft": false,
     *           "editAircraft": false,
     *           "manageMembers": false,
     *           "manageInviteCodes": false,
     *           "manageNotams": false,
     *           "editSettings": false
     *           }
     *         },
     *         "createdAt": "2026-01-01T00:00:00.000000Z",
     *         "updatedAt": "2026-01-01T00:00:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     */
    public function show(Request $request): UserResource
    {
        return (new UserResource($request->user()->load('airlines')))->withAbilities();
    }
}
