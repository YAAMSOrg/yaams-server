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
            $table->id(); # internal id
            $table->string("registration", 6);
            $table->string("manufacturer", 100);
            $table->string("model", 100);
            $table->string("current_loc", 4);
            $table->text("remarks")->nullable();
            $table->foreignId('used_by');
            $table->foreign('used_by')->references('id')->on('airlines')->nullable();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('aircraft');
    }
}
