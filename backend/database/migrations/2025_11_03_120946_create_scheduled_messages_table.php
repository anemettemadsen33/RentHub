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
        Schema::create('scheduled_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->onDelete('set null');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('recipient_type'); // guest, owner, all_guests
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('subject')->nullable();
            $table->text('content');
            $table->string('status')->default('pending'); // pending, sent, failed, cancelled
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['scheduled_at', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_messages');
    }
};
