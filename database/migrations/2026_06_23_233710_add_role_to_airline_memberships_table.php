<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airline_memberships', function (Blueprint $table) {
            $table->enum('role', ['Pilot', 'Dispatcher', 'Manager'])->default('Pilot')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('airline_memberships', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
