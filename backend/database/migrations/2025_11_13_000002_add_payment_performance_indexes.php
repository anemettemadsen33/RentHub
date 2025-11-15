<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for payments table
        $this->addIndexIfNotExists('payments', 'payments_booking_id_index', ['booking_id']);
        $this->addIndexIfNotExists('payments', 'payments_user_id_index', ['user_id']);
        $this->addIndexIfNotExists('payments', 'payments_status_index', ['status']);
        $this->addIndexIfNotExists('payments', 'payments_payment_method_index', ['payment_method']);
        $this->addIndexIfNotExists('payments', 'payments_created_at_index', ['created_at']);
        
        // Add composite indexes for common queries
        $this->addIndexIfNotExists('payments', 'payments_user_status_index', ['user_id', 'status']);
        $this->addIndexIfNotExists('payments', 'payments_booking_status_index', ['booking_id', 'status']);

        // Add indexes for invoices table
        $this->addIndexIfNotExists('invoices', 'invoices_payment_id_index', ['payment_id']);
        $this->addIndexIfNotExists('invoices', 'invoices_booking_id_index', ['booking_id']);
        $this->addIndexIfNotExists('invoices', 'invoices_user_id_index', ['user_id']);
        $this->addIndexIfNotExists('invoices', 'invoices_status_index', ['status']);
        $this->addIndexIfNotExists('invoices', 'invoices_invoice_number_index', ['invoice_number']);
        $this->addIndexIfNotExists('invoices', 'invoices_created_at_index', ['created_at']);
        
        // Add composite indexes for common queries
        $this->addIndexIfNotExists('invoices', 'invoices_user_status_index', ['user_id', 'status']);
        $this->addIndexIfNotExists('invoices', 'invoices_payment_status_index', ['payment_id', 'status']);

        // Add indexes for bank_accounts table
        $this->addIndexIfNotExists('bank_accounts', 'bank_accounts_is_active_index', ['is_active']);
        $this->addIndexIfNotExists('bank_accounts', 'bank_accounts_bank_name_index', ['bank_name']);
        $this->addIndexIfNotExists('bank_accounts', 'bank_accounts_created_at_index', ['created_at']);

        // Add indexes for payment_proofs table
        $this->addIndexIfNotExists('payment_proofs', 'payment_proofs_payment_id_index', ['payment_id']);
        $this->addIndexIfNotExists('payment_proofs', 'payment_proofs_status_index', ['status']);
        $this->addIndexIfNotExists('payment_proofs', 'payment_proofs_created_at_index', ['created_at']);
        
        // Add composite indexes for common queries
        $this->addIndexIfNotExists('payment_proofs', 'payment_proofs_payment_status_index', ['payment_id', 'status']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes for payments table
        $this->dropIndexIfExists('payments', 'payments_booking_id_index');
        $this->dropIndexIfExists('payments', 'payments_user_id_index');
        $this->dropIndexIfExists('payments', 'payments_status_index');
        $this->dropIndexIfExists('payments', 'payments_payment_method_index');
        $this->dropIndexIfExists('payments', 'payments_created_at_index');
        $this->dropIndexIfExists('payments', 'payments_user_status_index');
        $this->dropIndexIfExists('payments', 'payments_booking_status_index');

        // Remove indexes for invoices table
        $this->dropIndexIfExists('invoices', 'invoices_payment_id_index');
        $this->dropIndexIfExists('invoices', 'invoices_booking_id_index');
        $this->dropIndexIfExists('invoices', 'invoices_user_id_index');
        $this->dropIndexIfExists('invoices', 'invoices_status_index');
        $this->dropIndexIfExists('invoices', 'invoices_invoice_number_index');
        $this->dropIndexIfExists('invoices', 'invoices_created_at_index');
        $this->dropIndexIfExists('invoices', 'invoices_user_status_index');
        $this->dropIndexIfExists('invoices', 'invoices_payment_status_index');

        // Remove indexes for bank_accounts table
        $this->dropIndexIfExists('bank_accounts', 'bank_accounts_is_active_index');
        $this->dropIndexIfExists('bank_accounts', 'bank_accounts_bank_name_index');
        $this->dropIndexIfExists('bank_accounts', 'bank_accounts_created_at_index');

        // Remove indexes for payment_proofs table
        $this->dropIndexIfExists('payment_proofs', 'payment_proofs_payment_id_index');
        $this->dropIndexIfExists('payment_proofs', 'payment_proofs_status_index');
        $this->dropIndexIfExists('payment_proofs', 'payment_proofs_created_at_index');
        $this->dropIndexIfExists('payment_proofs', 'payment_proofs_payment_status_index');
    }

    /**
     * Add index if it doesn't already exist
     */
    private function addIndexIfNotExists(string $table, string $indexName, array $columns): void
    {
        $connection = DB::connection()->getDriverName();
        $indexExists = false;
        
        if ($connection === 'sqlite') {
            $existingIndexes = DB::select("PRAGMA index_list('{$table}')");
            foreach ($existingIndexes as $index) {
                if ($index->name === $indexName) {
                    $indexExists = true;
                    break;
                }
            }
        } else {
            // MySQL, PostgreSQL, etc.
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            $indexExists = !empty($indexes);
        }

        if (!$indexExists) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $connection = DB::connection()->getDriverName();
        $indexExists = false;
        
        if ($connection === 'sqlite') {
            $existingIndexes = DB::select("PRAGMA index_list('{$table}')");
            foreach ($existingIndexes as $index) {
                if ($index->name === $indexName) {
                    $indexExists = true;
                    break;
                }
            }
        } else {
            // MySQL, PostgreSQL, etc.
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            $indexExists = !empty($indexes);
        }

        if ($indexExists) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};