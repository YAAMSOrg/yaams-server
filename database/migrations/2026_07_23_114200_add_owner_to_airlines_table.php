<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airlines', function (Blueprint $table) {
            $table->foreignId('owner_user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        // Backfill
        $airlines = DB::table('airlines')->get();
        foreach ($airlines as $airline) {
            $ownerUserId = DB::table('airline_memberships')
                ->where('airline_id', $airline->id)
                ->where('role', 'Manager')
                ->orderBy('created_at')
                ->orderBy('id')
                ->value('user_id');

            if (!$ownerUserId) {
                $ownerUserId = DB::table('airline_memberships')
                    ->where('airline_id', $airline->id)
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->value('user_id');
            }

            if ($ownerUserId) {
                DB::table('airlines')
                    ->where('id', $airline->id)
                    ->update(['owner_user_id' => $ownerUserId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('airlines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_user_id');
        });
    }
};
