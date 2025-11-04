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
        Schema::create('smart_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // august, yale, schlage, nuki, etc.
            $table->string('lock_id')->unique(); // External lock ID from provider
            $table->string('name'); // e.g., "Front Door", "Main Entrance"
            $table->string('location')->nullable(); // Description of location
            $table->json('credentials')->nullable(); // Encrypted provider credentials
            $table->json('settings')->nullable(); // Lock-specific settings
            $table->enum('status', ['active', 'inactive', 'offline', 'error'])->default('active');
            $table->boolean('auto_generate_codes')->default(true); // Auto-generate for bookings
            $table->string('battery_level')->nullable(); // Battery status
            $table->timestamp('last_synced_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['property_id', 'status']);
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_locks');
    }
};
