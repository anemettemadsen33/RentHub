<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User Behavior Tracking pentru recommendations
        Schema::create('user_behaviors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // view, search, bookmark, book
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->json('metadata')->nullable(); // search criteria, time spent, etc
            $table->timestamp('action_at');
            $table->timestamps();

            $table->index(['user_id', 'action']);
            $table->index(['property_id', 'action']);
        });

        // Property Recommendations Cache
        Schema::create('property_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2); // recommendation score 0-100
            $table->string('reason'); // collaborative, content-based, hybrid
            $table->json('factors')->nullable(); // what influenced the recommendation
            $table->boolean('shown')->default(false);
            $table->boolean('clicked')->default(false);
            $table->boolean('booked')->default(false);
            $table->timestamp('valid_until');
            $table->timestamps();

            $table->unique(['user_id', 'property_id']);
            $table->index(['user_id', 'score']);
        });

        // ML Price Predictions
        Schema::create('price_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('predicted_price', 10, 2);
            $table->decimal('confidence', 5, 2); // 0-100%
            $table->json('features')->nullable(); // season, demand, events, etc
            $table->decimal('actual_price', 10, 2)->nullable();
            $table->decimal('actual_revenue', 10, 2)->nullable();
            $table->boolean('booked')->default(false);
            $table->string('model_version')->default('v1');
            $table->timestamps();

            $table->index(['property_id', 'date']);
        });

        // Revenue Optimization Suggestions
        Schema::create('revenue_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('suggestion_type'); // price_increase, price_decrease, minimum_stay, discount
            $table->text('description');
            $table->json('parameters'); // specific values
            $table->decimal('expected_impact', 10, 2); // revenue increase/decrease
            $table->decimal('confidence', 5, 2);
            $table->boolean('applied')->default(false);
            $table->timestamp('applied_at')->nullable();
            $table->decimal('actual_impact', 10, 2)->nullable();
            $table->timestamp('valid_until');
            $table->timestamps();

            $table->index(['property_id', 'suggestion_type']);
        });

        // Fraud Detection Alerts
        Schema::create('fraud_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type'); // suspicious_listing, payment_fraud, fake_review, bot_behavior
            $table->string('severity'); // low, medium, high, critical
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->json('evidence'); // detailed fraud indicators
            $table->decimal('fraud_score', 5, 2); // 0-100
            $table->string('status')->default('pending'); // pending, investigating, resolved, false_positive
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->boolean('action_taken')->default(false);
            $table->string('action_type')->nullable(); // account_suspended, property_removed, etc
            $table->timestamps();

            $table->index(['alert_type', 'severity', 'status']);
            $table->index(['fraud_score']);
        });

        // ML Model Performance Tracking
        Schema::create('ml_model_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('model_name'); // recommendation, price_optimization, fraud_detection
            $table->string('model_version');
            $table->string('metric_name'); // accuracy, precision, recall, f1_score, mae, rmse
            $table->decimal('metric_value', 10, 4);
            $table->json('metadata')->nullable();
            $table->timestamp('measured_at');
            $table->timestamps();

            $table->index(['model_name', 'model_version']);
        });

        // Similar Properties Cache (for comparison algorithm)
        Schema::create('similar_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('similar_property_id')->constrained('properties')->cascadeOnDelete();
            $table->decimal('similarity_score', 5, 2); // 0-100
            $table->json('similarity_factors'); // price, location, amenities, type
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['property_id', 'similar_property_id']);
            $table->index(['property_id', 'similarity_score']);
        });

        // Occupancy Predictions
        Schema::create('occupancy_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('prediction_date');
            $table->decimal('predicted_occupancy', 5, 2); // 0-100%
            $table->decimal('confidence', 5, 2);
            $table->json('factors'); // season, events, trends
            $table->boolean('actual_booked')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'prediction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupancy_predictions');
        Schema::dropIfExists('similar_properties');
        Schema::dropIfExists('ml_model_metrics');
        Schema::dropIfExists('fraud_alerts');
        Schema::dropIfExists('revenue_suggestions');
        Schema::dropIfExists('price_predictions');
        Schema::dropIfExists('property_recommendations');
        Schema::dropIfExists('user_behaviors');
    }
};
