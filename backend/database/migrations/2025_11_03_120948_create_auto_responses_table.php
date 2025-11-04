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
        Schema::create('auto_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('trigger_type'); // keyword, time_based, booking_event, inquiry
            $table->json('trigger_conditions'); // Keywords, time ranges, events
            $table->text('response_content');
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority responses checked first
            $table->integer('usage_count')->default(0);
            $table->timestamp('active_from')->nullable();
            $table->timestamp('active_until')->nullable();
            $table->json('settings')->nullable(); // Delay, max uses per user, etc.
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('trigger_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_responses');
    }
};
