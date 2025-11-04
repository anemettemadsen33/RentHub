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
        Schema::create('accounting_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('accounting_connection_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            $table->string('transaction_type'); // income, expense, refund
            $table->string('category'); // rental_income, cleaning_fee, maintenance, etc
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->string('external_id')->nullable(); // ID in accounting software
            $table->string('sync_status')->default('pending'); // pending, synced, failed
            $table->timestamp('synced_at')->nullable();
            $table->text('sync_error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'transaction_date']);
            $table->index('external_id');
            $table->index('sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_transactions');
    }
};
