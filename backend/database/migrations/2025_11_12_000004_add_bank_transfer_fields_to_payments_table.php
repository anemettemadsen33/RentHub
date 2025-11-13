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
        Schema::table('payments', function (Blueprint $table) {
            // Add bank transfer fields only if they don't exist
            if (!Schema::hasColumn('payments', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('transaction_id');
                $table->string('account_holder')->nullable();
                $table->string('account_number')->nullable();
                $table->string('transfer_reference')->nullable(); // User's transfer reference
                $table->timestamp('transfer_date')->nullable(); // When user claims to have made transfer
            }
        });
        
        // Drop Stripe columns if they exist (separate schema call for SQLite compatibility)
        if (Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex(['stripe_payment_intent_id']);
            });
        }
        
        if (Schema::hasColumn('payments', 'stripe_charge_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex(['stripe_charge_id']);
            });
        }
        
        Schema::table('payments', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
                $columns[] = 'stripe_payment_intent_id';
            }
            if (Schema::hasColumn('payments', 'stripe_charge_id')) {
                $columns[] = 'stripe_charge_id';
            }
            if (Schema::hasColumn('payments', 'stripe_customer_id')) {
                $columns[] = 'stripe_customer_id';
            }
            if (Schema::hasColumn('payments', 'dispute_reason')) {
                $columns[] = 'dispute_reason';
            }
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'bank_name')) {
                $table->dropColumn([
                    'bank_name',
                    'account_holder',
                    'account_number',
                    'transfer_reference',
                    'transfer_date',
                ]);
            }
        });
    }
};
