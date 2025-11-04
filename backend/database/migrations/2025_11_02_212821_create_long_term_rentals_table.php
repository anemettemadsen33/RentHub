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
        Schema::create('long_term_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            
            // Rental Period
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_months');
            $table->enum('rental_type', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            
            // Financial Details
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('security_deposit', 10, 2);
            $table->decimal('total_rent', 10, 2); // Total for entire period
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->integer('payment_day_of_month')->default(1); // Day of month for payment
            
            // Deposit Status
            $table->enum('deposit_status', ['pending', 'paid', 'held', 'returned', 'partially_returned'])->default('pending');
            $table->decimal('deposit_paid_amount', 10, 2)->nullable();
            $table->timestamp('deposit_paid_at')->nullable();
            $table->decimal('deposit_returned_amount', 10, 2)->nullable();
            $table->timestamp('deposit_returned_at')->nullable();
            
            // Lease Agreement
            $table->string('lease_agreement_path')->nullable();
            $table->timestamp('lease_signed_at')->nullable();
            $table->boolean('lease_auto_generated')->default(true);
            
            // Utilities Management
            $table->json('utilities_included')->nullable(); // ['electricity', 'water', 'gas', 'internet']
            $table->json('utilities_paid_by_tenant')->nullable();
            $table->decimal('utilities_estimate', 10, 2)->nullable();
            
            // Maintenance
            $table->boolean('maintenance_included')->default(false);
            $table->text('maintenance_terms')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'pending_approval', 'active', 'completed', 'cancelled', 'terminated'])->default('draft');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Renewal Options
            $table->boolean('auto_renewable')->default(false);
            $table->integer('renewal_notice_days')->default(30);
            $table->timestamp('renewal_requested_at')->nullable();
            $table->enum('renewal_status', ['not_requested', 'pending', 'approved', 'declined'])->default('not_requested');
            
            // Terms & Conditions
            $table->text('special_terms')->nullable();
            $table->json('house_rules')->nullable();
            $table->boolean('pets_allowed')->default(false);
            $table->boolean('smoking_allowed')->default(false);
            
            // Inspection
            $table->timestamp('move_in_inspection_at')->nullable();
            $table->timestamp('move_out_inspection_at')->nullable();
            $table->text('move_in_condition_notes')->nullable();
            $table->text('move_out_condition_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['property_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('long_term_rentals');
    }
};
