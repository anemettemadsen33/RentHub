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
        Schema::create('calendar_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_calendar_id')->constrained()->onDelete('cascade');
            $table->string('status', 50); // 'success', 'failed', 'partial'
            $table->integer('dates_added')->default(0);
            $table->integer('dates_removed')->default(0);
            $table->integer('dates_updated')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Additional sync details
            $table->timestamp('synced_at');
            $table->timestamps();
            
            $table->index('external_calendar_id');
            $table->index(['external_calendar_id', 'status']);
            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_sync_logs');
    }
};
