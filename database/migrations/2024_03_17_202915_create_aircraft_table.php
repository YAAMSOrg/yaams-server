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
            $table->id();
            $table->string("registration", 6);
            $table->string("manufacturer");
            $table->string("model");
            $table->string("current_loc", 4);
            $table->text("remarks");
            $table->foreignId('used_by')->constrained(
                table: 'airlines', indexName: 'id'
            );
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
