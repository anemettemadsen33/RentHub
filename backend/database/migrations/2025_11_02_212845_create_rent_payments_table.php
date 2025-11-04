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
        Schema::create('rent_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('long_term_rental_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            
            // Payment Details
            $table->enum('payment_type', ['deposit', 'monthly_rent', 'quarterly_rent', 'yearly_rent', 'utilities', 'late_fee', 'maintenance'])->default('monthly_rent');
            $table->integer('month_number')->nullable(); // Month 1, 2, 3...
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            
            // Amounts
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            
            // Status
            $table->enum('status', ['scheduled', 'pending', 'processing', 'paid', 'overdue', 'partial', 'failed', 'refunded'])->default('scheduled');
            $table->integer('days_overdue')->default(0);
            
            // Payment Method
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            
            // Notifications
            $table->timestamp('reminder_sent_at')->nullable();
            $table->integer('reminder_count')->default(0);
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['long_term_rental_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_payments');
    }
};
