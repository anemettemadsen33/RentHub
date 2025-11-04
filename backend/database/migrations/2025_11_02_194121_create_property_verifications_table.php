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
        Schema::create('property_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Property owner

            // Ownership Verification
            $table->enum('ownership_status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->string('ownership_document_type')->nullable(); // deed, lease_agreement, rental_contract
            $table->json('ownership_documents')->nullable(); // Array of document paths
            $table->timestamp('ownership_verified_at')->nullable();
            $table->text('ownership_rejection_reason')->nullable();

            // Property Inspection
            $table->enum('inspection_status', ['not_required', 'pending', 'scheduled', 'completed', 'failed'])->default('not_required');
            $table->timestamp('inspection_scheduled_at')->nullable();
            $table->timestamp('inspection_completed_at')->nullable();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('inspection_report')->nullable();
            $table->integer('inspection_score')->nullable(); // 0-100
            $table->text('inspection_notes')->nullable();

            // Property Photos Verification
            $table->enum('photos_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('photos_rejection_reason')->nullable();
            $table->timestamp('photos_verified_at')->nullable();

            // Property Details Verification
            $table->enum('details_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('details_to_correct')->nullable();
            $table->timestamp('details_verified_at')->nullable();

            // Legal Compliance
            $table->boolean('has_business_license')->default(false);
            $table->string('business_license_document')->nullable();
            $table->boolean('has_safety_certificate')->default(false);
            $table->string('safety_certificate_document')->nullable();
            $table->boolean('has_insurance')->default(false);
            $table->string('insurance_document')->nullable();
            $table->date('insurance_expiry_date')->nullable();

            // Overall Status
            $table->enum('overall_status', ['unverified', 'under_review', 'verified', 'rejected'])->default('unverified');
            $table->boolean('has_verified_badge')->default(false);
            $table->integer('verification_score')->default(0); // 0-100

            // Review Process
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();

            // Re-verification
            $table->date('next_verification_due')->nullable();
            $table->timestamp('last_verified_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('property_id');
            $table->index('user_id');
            $table->index('overall_status');
            $table->index('has_verified_badge');
            $table->index('next_verification_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_verifications');
    }
};
