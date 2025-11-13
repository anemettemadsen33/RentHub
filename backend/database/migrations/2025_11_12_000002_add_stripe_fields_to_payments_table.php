<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Stripe-specific fields
            $table->string('stripe_payment_intent_id')->nullable()->after('transaction_id');
            $table->string('stripe_charge_id')->nullable()->after('stripe_payment_intent_id');
            $table->string('stripe_customer_id')->nullable()->after('stripe_charge_id');
            $table->decimal('refunded_amount', 10, 2)->nullable()->after('refunded_at');
            $table->string('dispute_reason')->nullable()->after('refunded_amount');
            $table->timestamp('paid_at')->nullable()->after('completed_at');

            // Update status enum to include new statuses
            $table->enum('status', [
                'pending',
                'processing', 
                'completed',
                'failed',
                'refunded',
                'cancelled',
                'disputed'
            ])->default('pending')->change();

            // Indexes for Stripe fields
            $table->index('stripe_payment_intent_id');
            $table->index('stripe_charge_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropIndex(['stripe_charge_id']);
            
            $table->dropColumn([
                'stripe_payment_intent_id',
                'stripe_charge_id',
                'stripe_customer_id',
                'refunded_amount',
                'dispute_reason',
                'paid_at',
            ]);

            // Restore original status enum
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'refunded'
            ])->default('pending')->change();
        });
    }
};
