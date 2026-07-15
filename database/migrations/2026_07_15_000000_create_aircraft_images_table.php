<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aircraft_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_id')->constrained('aircraft')->cascadeOnDelete();
            // Relative path on the private `local` disk. The bytes are only ever
            // served through the authorized `aircraft.images.show` route.
            $table->string('path');
            // Exactly one primary per aircraft (the shot that best shows the
            // livery). Only ever set on an `approved` image.
            $table->boolean('is_primary')->default(false)->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            // Community moderation: pilot uploads start `pending` and are hidden
            // until a Manager approves them. Manager uploads are auto-approved.
            $table->string('status')->default('pending')->index();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aircraft_images');
    }
};
