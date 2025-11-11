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
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->enum('type', [
                'seasonal',      // Seasonal pricing
                'weekend',       // Weekend pricing
                'holiday',       // Holiday pricing
                'demand',        // Demand-based pricing
                'last_minute',   // Last-minute discounts
                'early_bird',    // Early booking discounts
                'weekly',        // Weekly discount
                'monthly',       // Monthly discount
                'minimum_stay',  // Discount applied when staying >= min_nights
            ]);
            $table->string('name'); // e.g., "Summer Season", "Christmas Holiday"
            $table->text('description')->nullable();

            // Date range
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Days of week (for weekend/weekday pricing)
            $table->json('days_of_week')->nullable(); // [0-6] where 0=Sunday

            // Pricing adjustment
            $table->enum('adjustment_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('adjustment_value', 10, 2); // +20% or +50€

            // Minimum stay requirement
            $table->integer('min_nights')->nullable();
            $table->integer('max_nights')->nullable();

            // Conditions
            $table->integer('advance_booking_days')->nullable(); // For early bird
            $table->integer('last_minute_days')->nullable(); // For last minute (e.g., < 7 days)

            // Priority (higher priority rules override lower)
            $table->integer('priority')->default(0);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('property_id');
            $table->index(['start_date', 'end_date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
