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
        Schema::create('cleaning_services', function (Blueprint $table) {
            $table->id();

            // References
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('long_term_rental_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('service_provider_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // Owner/Manager who requested

            // Service Details
            $table->enum('service_type', [
                'regular_cleaning',
                'deep_cleaning',
                'move_in',
                'move_out',
                'post_booking',
                'emergency',
                'custom',
            ])->default('regular_cleaning');

            $table->text('description')->nullable();
            $table->json('checklist')->nullable(); // Cleaning tasks checklist
            $table->text('special_instructions')->nullable();

            // Scheduling
            $table->timestamp('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->integer('estimated_duration_hours')->default(2);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Access
            $table->boolean('requires_key')->default(false);
            $table->text('access_instructions')->nullable();
            $table->string('access_code')->nullable(); // Smart lock code if applicable

            // Status
            $table->enum('status', [
                'scheduled',
                'confirmed',
                'in_progress',
                'completed',
                'cancelled',
                'needs_rescheduling',
            ])->default('scheduled');

            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Completion Details
            $table->json('completed_checklist')->nullable(); // What was actually done
            $table->json('before_photos')->nullable();
            $table->json('after_photos')->nullable();
            $table->text('completion_notes')->nullable();
            $table->json('issues_found')->nullable(); // Any problems discovered during cleaning

            // Rating & Feedback
            $table->integer('rating')->nullable(); // 1-5 stars
            $table->text('feedback')->nullable();
            $table->timestamp('rated_at')->nullable();

            // Cost
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            // Supplies
            $table->boolean('provider_brings_supplies')->default(true);
            $table->json('supplies_needed')->nullable(); // If specific supplies required

            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'status']);
            $table->index(['service_provider_id', 'status']);
            $table->index('scheduled_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_services');
    }
};
