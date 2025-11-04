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
        Schema::create('loyalty_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tier_id')->constrained('loyalty_tiers')->onDelete('cascade');
            $table->enum('benefit_type', [
                'discount',
                'priority_support',
                'free_cancellation',
                'early_access',
                'exclusive_properties',
                'personal_concierge',
                'airport_pickup',
                'late_checkout',
                'welcome_gift',
                'other'
            ]);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('value')->nullable(); // e.g., "15%", "24h", "yes"
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['tier_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_benefits');
    }
};
