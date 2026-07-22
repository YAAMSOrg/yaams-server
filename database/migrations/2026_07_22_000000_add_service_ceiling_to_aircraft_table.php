<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aircraft', function (Blueprint $table) {
            // Service ceiling in feet; nullable - when unset, no per-aircraft
            // altitude limit is enforced on PIREP filing.
            $table->integer('service_ceiling')->nullable()->after('mlw');
        });
    }

    public function down(): void
    {
        Schema::table('aircraft', function (Blueprint $table) {
            $table->dropColumn('service_ceiling');
        });
    }
};
