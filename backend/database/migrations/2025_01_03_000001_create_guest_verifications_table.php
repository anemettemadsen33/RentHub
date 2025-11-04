<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Identity Verification
            $table->enum('identity_status', ['pending', 'verified', 'rejected', 'expired'])->default('pending');
            $table->string('document_type')->nullable(); // passport, drivers_license, id_card
            $table->string('document_number')->nullable();
            $table->string('document_front')->nullable(); // file path
            $table->string('document_back')->nullable(); // file path
            $table->string('selfie_photo')->nullable(); // for liveness check
            $table->date('document_expiry_date')->nullable();
            $table->timestamp('identity_verified_at')->nullable();
            $table->text('identity_rejection_reason')->nullable();

            // Credit Check (optional)
            $table->boolean('credit_check_enabled')->default(false);
            $table->enum('credit_status', ['not_requested', 'pending', 'approved', 'rejected'])->default('not_requested');
            $table->integer('credit_score')->nullable();
            $table->text('credit_report')->nullable();
            $table->timestamp('credit_checked_at')->nullable();

            // Background Check
            $table->enum('background_status', ['pending', 'clear', 'flagged'])->default('pending');
            $table->text('background_notes')->nullable();
            $table->timestamp('background_checked_at')->nullable();

            // References
            $table->json('references')->nullable(); // Array of references
            $table->integer('references_verified')->default(0);

            // Guest Rating/Score
            $table->decimal('trust_score', 3, 2)->default(0.00); // 0.00 to 5.00
            $table->integer('completed_bookings')->default(0);
            $table->integer('cancelled_bookings')->default(0);
            $table->integer('positive_reviews')->default(0);
            $table->integer('negative_reviews')->default(0);

            // Verification History
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('identity_status');
            $table->index('credit_status');
            $table->index('background_status');
            $table->index('trust_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_verifications');
    }
};
