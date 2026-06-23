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
        Schema::table('aircraft', function (Blueprint $table) {
            $table->string('engine_type')->nullable()->after('model');
            $table->boolean('satcom')->default(false)->after('engine_type');
            $table->boolean('winglets')->default(false)->after('satcom');
            $table->string('selcal', 5)->nullable()->after('winglets');
            $table->string('hex_code', 6)->nullable()->after('selcal');
            $table->string('msn', 50)->nullable()->after('hex_code');
            $table->integer('mtow')->nullable()->after('msn');
            $table->integer('mzfw')->nullable()->after('mtow');
            $table->integer('mlw')->nullable()->after('mzfw');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aircraft', function (Blueprint $table) {
            $table->dropColumn([
                'engine_type',
                'satcom',
                'winglets',
                'selcal',
                'hex_code',
                'msn',
                'mtow',
                'mzfw',
                'mlw'
            ]);
        });
    }
};
