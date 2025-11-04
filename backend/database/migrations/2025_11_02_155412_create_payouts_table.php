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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('payout_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Owner/Agent
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bank_account_id')->nullable()->constrained()->onDelete('set null');

            // Payout Details
            $table->decimal('booking_amount', 10, 2); // Original booking amount
            $table->decimal('commission_rate', 5, 2)->default(0); // Percentage (e.g., 15.00 for 15%)
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('payout_amount', 10, 2); // Amount to pay owner
            $table->string('currency', 3)->default('EUR');

            // Status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');

            // Schedule
            $table->date('payout_date'); // Scheduled payout date
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Payment Details
            $table->string('payment_method')->nullable(); // bank_transfer, paypal, etc
            $table->string('transaction_reference')->nullable();

            // Period covered
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();

            $table->text('failure_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('booking_id');
            $table->index('status');
            $table->index('payout_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
