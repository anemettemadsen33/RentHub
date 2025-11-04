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
        Schema::create('guest_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');

            // Screening status
            $table->enum('status', ['pending', 'in_progress', 'approved', 'rejected', 'expired'])->default('pending');
            $table->enum('risk_level', ['low', 'medium', 'high', 'unknown'])->default('unknown');
            $table->integer('screening_score')->nullable(); // 0-100

            // Identity verification
            $table->boolean('identity_verified')->default(false);
            $table->timestamp('identity_verified_at')->nullable();
            $table->string('identity_verification_method')->nullable(); // passport, id_card, drivers_license

            // Phone verification
            $table->boolean('phone_verified')->default(false);
            $table->timestamp('phone_verified_at')->nullable();

            // Email verification
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();

            // Credit check
            $table->boolean('credit_check_completed')->default(false);
            $table->timestamp('credit_check_completed_at')->nullable();
            $table->string('credit_score')->nullable();
            $table->enum('credit_rating', ['excellent', 'good', 'fair', 'poor', 'none'])->nullable();

            // Background check
            $table->boolean('background_check_completed')->default(false);
            $table->timestamp('background_check_completed_at')->nullable();
            $table->boolean('background_check_passed')->nullable();

            // References
            $table->integer('references_count')->default(0);
            $table->integer('references_verified')->default(0);

            // Guest ratings
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('total_bookings')->default(0);
            $table->integer('completed_bookings')->default(0);
            $table->integer('cancelled_bookings')->default(0);

            // Review notes
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Expiry
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('risk_level');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_screenings');
    }
};
