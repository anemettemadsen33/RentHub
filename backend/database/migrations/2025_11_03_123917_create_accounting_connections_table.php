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
        Schema::create('accounting_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // quickbooks, xero
            $table->string('status')->default('disconnected'); // connected, disconnected, error
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('realm_id')->nullable(); // QuickBooks company ID
            $table->string('tenant_id')->nullable(); // Xero organization ID
            $table->json('settings')->nullable();
            $table->boolean('auto_sync')->default(true);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_connections');
    }
};
