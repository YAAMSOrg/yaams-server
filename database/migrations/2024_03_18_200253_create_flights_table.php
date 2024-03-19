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
            $table->foreignId('airline');
            $table->foreign('airline')->references('id')->on('airlines');
            $table->string('callsign', 4);
            $table->integer('flightnumber');
            $table->string('departure_icao', 4);
            $table->string('arrival_icao', 4);
            $table->string('aircraft', 6);
            $table->foreign('aircraft')->references('registration')->on('aircraft');
            $table->integer('crzalt');
            $table->datetime('blockoff');
            $table->datetime('blockon');
            $table->integer('burned_fuel');
            $table->text('route');
            $table->foreignId('online_network');
            $table->foreign('online_network')->references('id')->on('online_networks');
            $table->foreignId('pilot');
            $table->foreign('pilot')->references('id')->on('users');
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
