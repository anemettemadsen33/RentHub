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
        Schema::create('channel_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('channel'); // airbnb, booking_com, vrbo, expedia
            $table->string('status')->default('disconnected'); // connected, disconnected, error, pending
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('credentials')->nullable(); // API keys, account IDs
            $table->json('settings')->nullable(); // Sync preferences
            $table->boolean('auto_sync_calendar')->default(true);
            $table->boolean('auto_sync_pricing')->default(true);
            $table->boolean('auto_sync_availability')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'channel']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_connections');
    }
};
