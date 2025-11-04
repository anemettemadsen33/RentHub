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
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['earned', 'redeemed', 'expired', 'bonus', 'adjustment', 'refund']);
            $table->integer('points'); // Can be negative for redemptions
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable(); // Polymorphic reference
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('expires_at')->nullable(); // For earned points
            $table->boolean('is_expired')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'type']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
