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
        Schema::create('screening_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_screening_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            
            $table->enum('document_type', [
                'passport',
                'id_card',
                'drivers_license',
                'proof_of_income',
                'employment_letter',
                'bank_statement',
                'utility_bill',
                'other'
            ]);
            $table->string('document_number')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->integer('file_size');
            
            // Verification
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            
            // Document metadata
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('issuing_country')->nullable();
            $table->string('issuing_authority')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('guest_screening_id');
            $table->index('document_type');
            $table->index('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screening_documents');
    }
};
