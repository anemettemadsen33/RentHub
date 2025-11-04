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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('referral_code', 20)->unique();
            $table->string('referred_email')->nullable();
            $table->string('status')->default('pending'); // pending, registered, completed, expired
            $table->integer('referrer_reward_points')->default(0);
            $table->integer('referred_reward_points')->default(0);
            $table->decimal('referrer_reward_amount', 10, 2)->default(0);
            $table->decimal('referred_reward_amount', 10, 2)->default(0);
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('metadata')->nullable(); // JSON for additional tracking
            $table->timestamps();
            
            $table->index('referral_code');
            $table->index('status');
            $table->index(['referrer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
