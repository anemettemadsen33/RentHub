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
        Schema::create('guest_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_screening_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('guest_verification_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Reference details
            $table->string('reference_name');
            $table->string('reference_email')->nullable();
            $table->string('reference_phone')->nullable();
            $table->string('reference_type')->nullable(); // For compatibility with tests
            $table->enum('relationship', [
                'previous_landlord',
                'employer',
                'colleague',
                'friend',
                'family',
                'other',
            ])->nullable();
            $table->text('relationship_description')->nullable();

            // Verification
            $table->enum('status', ['pending', 'contacted', 'verified', 'failed', 'expired'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->string('verification_code')->nullable();
            $table->string('verification_token')->nullable(); // For compatibility

            // Reference response
            $table->boolean('responded')->default(false);
            $table->timestamp('responded_at')->nullable();
            $table->integer('rating')->nullable(); // 1-5
            $table->text('comments')->nullable();

            // Questions & Answers
            $table->boolean('would_rent_again')->nullable();
            $table->boolean('reliable_tenant')->nullable();
            $table->boolean('damages_caused')->nullable();
            $table->boolean('payment_issues')->nullable();
            $table->text('strengths')->nullable();
            $table->text('concerns')->nullable();

            // Contact attempts
            $table->integer('contact_attempts')->default(0);
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('guest_screening_id');
            $table->index('guest_verification_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('verification_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_references');
    }
};
