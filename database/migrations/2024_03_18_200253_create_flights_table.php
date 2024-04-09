<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id(); # internal flight id
            $table->foreignId('airline_id');
            $table->foreign('airline_id')->references('id')->on('airlines');
            $table->string('callsign', 4);
            $table->integer('flightnumber');
            $table->string('departure_icao', 4);
            $table->foreign('departure_icao')->references('icao_code')->on('airports');
            $table->string('arrival_icao', 4);
            $table->foreign('arrival_icao')->references('icao_code')->on('airports');
            $table->foreignId('aircraft_id');
            $table->foreign('aircraft_id')->references('id')->on('aircraft');
            $table->integer('crzalt');
            $table->datetime('blockoff');
            $table->datetime('blockon');
            $table->integer('burned_fuel');
            $table->text('route');
            $table->foreignId('online_network_id');
            $table->foreign('online_network_id')->references('id')->on('online_networks');
            $table->foreignId('pilot_id');
            $table->foreign('pilot_id')->references('id')->on('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flights');
    }
}
