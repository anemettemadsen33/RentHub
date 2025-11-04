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
        Schema::create('verification_documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('verifiable'); // user_verification or property_verification
            $table->enum('document_type', [
                'id_card',
                'passport',
                'driving_license',
                'selfie',
                'address_proof',
                'ownership_deed',
                'lease_agreement',
                'business_license',
                'safety_certificate',
                'insurance_document',
                'bank_statement',
                'tax_document',
                'other',
            ]);
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable(); // mime type
            $table->integer('file_size')->nullable(); // bytes
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('metadata')->nullable(); // Additional info like expiry date, document number, etc.
            $table->timestamps();
            $table->softDeletes();

            // Indexes (morphs() already creates index for verifiable_type and verifiable_id)
            $table->index('document_type');
            $table->index('status');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_documents');
    }
};
