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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Payer
            
            // Payment Details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->enum('type', ['full', 'deposit', 'balance', 'refund'])->default('full');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            
            // Payment Method
            $table->string('payment_method'); // bank_transfer, paypal, cash
            $table->string('payment_gateway')->nullable(); // paypal, etc
            $table->string('transaction_id')->nullable();
            $table->string('gateway_reference')->nullable();
            
            // Bank Transfer Details
            $table->string('bank_reference')->nullable();
            $table->text('bank_receipt')->nullable(); // File path
            
            // Timeline
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            // Additional Info
            $table->text('failure_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // For gateway-specific data
            
            $table->timestamps();
            
            // Indexes
            $table->index('booking_id');
            $table->index('invoice_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
