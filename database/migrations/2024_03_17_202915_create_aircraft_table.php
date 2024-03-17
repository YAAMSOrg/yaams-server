<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAircraftTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aircraft', function (Blueprint $table) {
            $table->string("registration", 6)->primary();
            $table->string("manufacturer");
            $table->string("model");
            $table->string("current_loc", 4)->nullable();
            $table->text("remarks");
            $table->foreignId('used_by');
            $table->foreign('used_by')->references('id')->on('airlines')->nullable();
            $table->date("in_service_since");
            $table->boolean("active");
            $table->timestamp("last_modified");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aircraft');
    }
}
