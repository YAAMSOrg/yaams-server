<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->string('icao_code', 4)->primary();
            $table->string('name', 80)->nullable();
            $table->decimal('latitude_deg');
            $table->decimal('longitude_deg');
            $table->integer('elevation_ft');
            $table->string('iso_country', 11);
            $table->string('iata_code', 3)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
