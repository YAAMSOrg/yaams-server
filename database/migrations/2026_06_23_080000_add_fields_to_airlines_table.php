<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airlines', function (Blueprint $table) {
            $table->string('hub', 4)->nullable()->after('atc_callsign');
            $table->foreign('hub')->references('icao_code')->on('airports')->nullOnDelete();
            $table->string('country', 2)->after('hub');
            $table->text('description')->nullable()->after('country');
            $table->string('website', 255)->nullable()->after('description');
            $table->date('founded_at')->nullable()->after('website');
            $table->boolean('active')->default(true)->after('founded_at');
            $table->boolean('require_pirep_review')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('airlines', function (Blueprint $table) {
            $table->dropForeign(['hub']);
            $table->dropColumn(['hub', 'country', 'description', 'website', 'founded_at', 'active', 'require_pirep_review']);
        });
    }
};
