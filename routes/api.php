<?php

use App\Http\Controllers\Api\V1\AircraftAPIController;
use App\Http\Controllers\Api\V1\AirlineAPIController;
use App\Http\Controllers\Api\V1\FlightAPIController;
use App\Http\Controllers\Api\V1\InfoController;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
| JSON API under /api/v1, authenticated via Sanctum tokens. Aircraft and
| flights are nested resources of airlines, to mirror the active-airline
| scope of the web UI.
|
*/

// Public instance metadata - intentionally outside auth:sanctum so clients
// can identify the instance before the user has a token.
Route::get('v1/info', [InfoController::class, 'index'])->name('api.v1.info');

Route::group(['prefix' => 'v1', 'as' => 'api.v1.', 'middleware' => 'auth:sanctum'], function () {
    // "Who am I" - token sanity check for API clients.
    Route::get('user', function (Request $request) {
        return new UserResource($request->user()->load('airlines'));
    })->name('user');

    // Only the actions AirlineAPIController implements - update/destroy do not exist.
    Route::apiResource('airlines', AirlineAPIController::class)->only(['index', 'show', 'store']);

    // scoped() resolves {aircraft} through the airline's aircraft() relation,
    // so an aircraft outside {airline} is a 404.
    Route::apiResource('airlines.aircraft', AircraftAPIController::class)
        ->only(['index', 'store', 'show'])
        ->scoped();

    // Flights / PIREPs
    Route::get('airlines/{airline}/flights', [FlightAPIController::class, 'index'])->name('airlines.flights.index');
    Route::post('airlines/{airline}/flights', [FlightAPIController::class, 'store'])->name('airlines.flights.store');
    Route::get('airlines/{airline}/flights/review', [FlightAPIController::class, 'reviewList'])->name('airlines.flights.review');
    Route::post('flights/{flight}/accept', [FlightAPIController::class, 'accept'])->name('flights.accept');
    Route::post('flights/{flight}/reject', [FlightAPIController::class, 'reject'])->name('flights.reject');
});
