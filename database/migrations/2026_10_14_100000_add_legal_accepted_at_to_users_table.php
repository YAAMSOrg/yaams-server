<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // When the user accepted the privacy policy (and terms, if published)
        // at registration. Null for accounts that predate the consent checkbox.
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('legal_accepted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('legal_accepted_at');
        });
    }
};
