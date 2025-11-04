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
        Schema::create('google_calendar_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('cascade');

            // OAuth tokens
            $table->text('access_token');
            $table->text('refresh_token');
            $table->string('token_type')->default('Bearer');
            $table->timestamp('expires_at');

            // Calendar info
            $table->string('calendar_id');
            $table->string('calendar_name')->nullable();

            // Webhook info
            $table->string('webhook_id')->nullable();
            $table->string('webhook_resource_id')->nullable();
            $table->timestamp('webhook_expiration')->nullable();

            // Sync settings
            $table->boolean('sync_enabled')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->json('sync_errors')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'property_id']);
            $table->index('calendar_id');
            $table->index('sync_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_calendar_tokens');
    }
};
