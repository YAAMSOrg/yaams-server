<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('aircraft', function (Blueprint $table) {
            $table->string('status')->default('active')->index()->after('active');
            $table->timestamp('retired_at')->nullable()->after('status');
            $table->string('retired_reason')->nullable()->after('retired_at');
        });

        // Backfill the new lifecycle state from the old boolean.
        DB::table('aircraft')->where('active', true)->update(['status' => 'active']);
        DB::table('aircraft')->where('active', false)->update(['status' => 'inactive']);

        Schema::table('aircraft', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aircraft', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('remarks');
        });

        DB::table('aircraft')->where('status', 'active')->update(['active' => true]);
        DB::table('aircraft')->where('status', '<>', 'active')->update(['active' => false]);

        Schema::table('aircraft', function (Blueprint $table) {
            $table->dropColumn(['status', 'retired_at', 'retired_reason']);
        });
    }
};
