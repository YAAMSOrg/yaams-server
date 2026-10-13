<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airlines', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
        });

        // The `active` flag only hid an airline from the landing page; suspension
        // (suspended_at) replaces it. Carry over airlines that were switched off.
        if (Schema::hasColumn('airlines', 'active')) {
            DB::table('airlines')->where('active', false)->update(['suspended_at' => now()]);

            Schema::table('airlines', function (Blueprint $table) {
                $table->dropColumn('active');
            });
        }
    }

    public function down(): void
    {
        Schema::table('airlines', function (Blueprint $table) {
            $table->boolean('active')->default(true);
        });

        DB::table('airlines')->whereNotNull('suspended_at')->update(['active' => false]);

        Schema::table('airlines', function (Blueprint $table) {
            $table->dropColumn(['suspended_at', 'suspension_reason']);
        });
    }
};
