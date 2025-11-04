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
        Schema::create('lock_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_lock_id')->constrained()->onDelete('cascade');
            $table->foreignId('access_code_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('event_type', ['unlock', 'lock', 'code_used', 'code_created', 'code_deleted', 'battery_low', 'error', 'manual_override']);
            $table->string('code_used')->nullable(); // The actual code that was used
            $table->enum('access_method', ['code', 'app', 'key', 'remote', 'auto'])->nullable();
            $table->json('metadata')->nullable(); // Additional event data
            $table->text('description')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('event_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['smart_lock_id', 'event_at']);
            $table->index(['user_id', 'event_at']);
            $table->index(['event_type', 'event_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lock_activities');
    }
};
