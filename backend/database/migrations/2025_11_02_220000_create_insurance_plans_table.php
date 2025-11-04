<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['cancellation', 'damage', 'liability', 'travel', 'comprehensive']);
            $table->decimal('price_per_night', 10, 2)->default(0);
            $table->decimal('price_percentage', 5, 2)->default(0)->comment('Percentage of booking total');
            $table->decimal('fixed_price', 10, 2)->default(0);
            $table->decimal('max_coverage', 12, 2)->comment('Maximum coverage amount');
            $table->json('coverage_details')->nullable();
            $table->json('exclusions')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_mandatory')->default(false);
            $table->integer('min_nights')->default(1)->comment('Minimum nights required');
            $table->integer('max_nights')->nullable()->comment('Maximum nights allowed');
            $table->decimal('min_booking_value', 10, 2)->default(0);
            $table->decimal('max_booking_value', 12, 2)->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('type');
            $table->index(['is_active', 'is_mandatory']);
        });

        Schema::create('booking_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('insurance_plan_id')->constrained()->onDelete('restrict');
            $table->string('policy_number')->unique();
            $table->enum('status', ['pending', 'active', 'claimed', 'expired', 'cancelled'])->default('pending');
            $table->decimal('premium_amount', 10, 2);
            $table->decimal('coverage_amount', 12, 2);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->json('coverage_details')->nullable();
            $table->json('policy_document_url')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('booking_id');
            $table->index('policy_number');
            $table->index('status');
        });

        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_insurance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('claim_number')->unique();
            $table->enum('type', ['cancellation', 'damage', 'injury', 'theft', 'other']);
            $table->enum('status', ['submitted', 'under_review', 'approved', 'rejected', 'paid'])->default('submitted');
            $table->text('description');
            $table->decimal('claimed_amount', 10, 2);
            $table->decimal('approved_amount', 10, 2)->nullable();
            $table->date('incident_date');
            $table->json('supporting_documents')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('claim_number');
            $table->index('booking_insurance_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('booking_insurances');
        Schema::dropIfExists('insurance_plans');
    }
};
