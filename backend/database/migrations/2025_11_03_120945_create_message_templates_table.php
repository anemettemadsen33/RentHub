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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('category')->nullable(); // booking_confirmation, check_in, check_out, inquiry, etc.
            $table->string('trigger_event')->nullable(); // booking_confirmed, before_checkin, after_checkout, etc.
            $table->text('subject')->nullable();
            $table->text('content');
            $table->json('variables')->nullable(); // Available variables for template
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'category']);
            $table->index('trigger_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
