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
        Schema::create('price_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');

            // Suggestion for specific date range
            $table->date('start_date');
            $table->date('end_date');

            // Current vs Suggested pricing
            $table->decimal('current_price', 10, 2);
            $table->decimal('suggested_price', 10, 2);
            $table->decimal('min_recommended_price', 10, 2)->nullable();
            $table->decimal('max_recommended_price', 10, 2)->nullable();

            // Confidence score (0-100)
            $table->integer('confidence_score')->default(0);

            // Reasoning factors (AI analysis)
            $table->json('factors')->nullable(); // Market data, competitor prices, demand, etc.

            // Market analysis
            $table->decimal('market_average_price', 10, 2)->nullable();
            $table->integer('competitor_count')->default(0);
            $table->decimal('occupancy_rate', 5, 2)->nullable(); // Area occupancy %
            $table->integer('demand_score')->nullable(); // 1-100

            // Historical data
            $table->decimal('historical_price', 10, 2)->nullable();
            $table->decimal('historical_occupancy', 5, 2)->nullable();

            // Status
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // AI Model info
            $table->string('model_version')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('property_id');
            $table->index(['start_date', 'end_date']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_suggestions');
    }
};
