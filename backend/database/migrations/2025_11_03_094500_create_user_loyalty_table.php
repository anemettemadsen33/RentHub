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
        Schema::create('user_loyalty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('current_tier_id')->nullable()->constrained('loyalty_tiers')->onDelete('set null');
            $table->integer('total_points_earned')->default(0); // Lifetime points earned
            $table->integer('available_points')->default(0); // Current spendable points
            $table->integer('redeemed_points')->default(0); // Total redeemed
            $table->integer('expired_points')->default(0); // Total expired
            $table->timestamp('tier_achieved_at')->nullable();
            $table->integer('next_tier_points')->nullable(); // Points needed for next tier
            $table->date('last_birthday_bonus_at')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_loyalty');
    }
};
