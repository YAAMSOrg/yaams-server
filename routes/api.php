<?php

use App\Http\Controllers\Api\V1\AircraftAPIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\ApiTestController;
use App\Http\Controllers\Api\V1\AirlineAPIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// API V1
// Route Group for api/v1 PROTECTED routes

Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\Api\V1', 'middleware' => 'auth:sanctum' ], function() {
    Route::get('apitest', [ApiTestController::class, 'index'])->name('apitest');

    // Can receive GET or POST.
    Route::apiResource('airlines', AirlineAPIController::class);

    Route::get('/airline/{airline}/aircraft/', [AircraftAPIController::class, 'listAircraftForAirline'])->name('aircraftlistforairline');
    Route::post('/airline/{airline}/aircraft/', [AircraftAPIController::class, 'addAircraftForAirline'])->name('aircraftaddforairline');

    // Flight API
    Route::get('/flights', [\App\Http\Controllers\Api\V1\FlightAPIController::class, 'index'])->name('api.flights.index');
    Route::post('/flights', [\App\Http\Controllers\Api\V1\FlightAPIController::class, 'store'])->name('api.flights.store');
    Route::get('/airline/{airline}/flights/review', [\App\Http\Controllers\Api\V1\FlightAPIController::class, 'reviewList'])->name('api.flights.review');
    Route::post('/flights/{flight}/accept', [\App\Http\Controllers\Api\V1\FlightAPIController::class, 'accept'])->name('api.flights.accept');
    Route::post('/flights/{flight}/reject', [\App\Http\Controllers\Api\V1\FlightAPIController::class, 'reject'])->name('api.flights.reject');

});
