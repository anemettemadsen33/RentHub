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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Notification type: booking, payment, review, account, system
            $table->string('notification_type');

            // Channels
            $table->boolean('channel_email')->default(true);
            $table->boolean('channel_database')->default(true);
            $table->boolean('channel_sms')->default(false);
            $table->boolean('channel_push')->default(false);

            $table->timestamps();

            // Unique constraint: one preference per user per notification type
            $table->unique(['user_id', 'notification_type']);

            // Indexes
            $table->index('user_id');
            $table->index('notification_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
