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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // For agent/owner accounts
            $table->string('account_name'); // Company/Person name
            $table->string('account_holder_name');
            $table->string('iban')->unique();
            $table->string('bic_swift');
            $table->string('bank_name');
            $table->string('bank_address')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('account_type')->default('business'); // business, personal
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index for faster queries
            $table->index('user_id');
            $table->index('is_default');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
