<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
        });

        // The legacy `isactive` flag was never read anywhere; suspension
        // (suspended_at) replaces it.
        if (Schema::hasColumn('users', 'isactive')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('isactive');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['suspended_at', 'suspension_reason']);
            $table->boolean('isactive')->default(true);
        });
    }
};
