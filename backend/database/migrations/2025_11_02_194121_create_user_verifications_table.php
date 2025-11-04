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
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ID Verification
            $table->enum('id_verification_status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->string('id_document_type')->nullable(); // passport, driving_license, national_id
            $table->string('id_document_number')->nullable();
            $table->string('id_front_image')->nullable();
            $table->string('id_back_image')->nullable();
            $table->string('selfie_image')->nullable();
            $table->timestamp('id_verified_at')->nullable();
            $table->text('id_rejection_reason')->nullable();

            // Phone Verification
            $table->enum('phone_verification_status', ['pending', 'verified'])->default('pending');
            $table->string('phone_number')->nullable();
            $table->string('phone_verification_code')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('phone_verification_code_sent_at')->nullable();

            // Email Verification
            $table->enum('email_verification_status', ['pending', 'verified'])->default('pending');
            $table->timestamp('email_verified_at')->nullable();

            // Address Verification
            $table->enum('address_verification_status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->text('address')->nullable();
            $table->string('address_proof_document')->nullable(); // utility_bill, bank_statement, etc.
            $table->string('address_proof_image')->nullable();
            $table->timestamp('address_verified_at')->nullable();
            $table->text('address_rejection_reason')->nullable();

            // Background Check (optional)
            $table->enum('background_check_status', ['not_requested', 'pending', 'in_progress', 'completed', 'failed'])->default('not_requested');
            $table->string('background_check_provider')->nullable();
            $table->string('background_check_reference')->nullable();
            $table->json('background_check_result')->nullable();
            $table->timestamp('background_check_completed_at')->nullable();

            // Overall Status
            $table->enum('overall_status', ['unverified', 'partially_verified', 'fully_verified'])->default('unverified');
            $table->integer('verification_score')->default(0); // 0-100

            // Admin Notes
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('overall_status');
            $table->index('id_verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_verifications');
    }
};
