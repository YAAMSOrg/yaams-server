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
            $table->id(); # internal id
            //$table->string('airline'); # TODO: Foreign!!
            $table->integer('flightnumber');
            $table->string('departure_icao', 4);
            $table->string('arrival_icao', 4);
            //$table->string('aircraft'); # TODO: Foreign!!
            $table->string('callsign', 7);
            $table->integer('crzalt');
            $table->datetime('blockoff');
            $table->datetime('blockon');
            $table->integer('burned_fuel');
            $table->text('route');
            $table->foreign('online_network_id')->references('id')->on('online_networks'); # TODO: Foreign!!
            $table->text('remarks');
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
