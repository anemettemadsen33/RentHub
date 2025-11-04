<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Summer Vacation in Bucharest"

            // Search criteria (stored as JSON)
            $table->json('criteria')->nullable();

            // Search location
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('radius_km')->nullable();

            // Price range
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();

            // Property filters
            $table->integer('min_bedrooms')->nullable();
            $table->integer('max_bedrooms')->nullable();
            $table->integer('min_bathrooms')->nullable();
            $table->integer('max_bathrooms')->nullable();
            $table->integer('min_guests')->nullable();
            $table->string('property_type')->nullable();

            // Amenities filter (array)
            $table->json('amenities')->nullable();

            // Date filters
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();

            // Alert settings
            $table->boolean('enable_alerts')->default(true);
            $table->enum('alert_frequency', ['instant', 'daily', 'weekly'])->default('daily');
            $table->timestamp('last_alert_sent_at')->nullable();
            $table->integer('new_listings_count')->default(0); // Count since last alert

            // Metadata
            $table->boolean('is_active')->default(true);
            $table->integer('search_count')->default(0); // How many times used
            $table->timestamp('last_searched_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('is_active');
            $table->index(['latitude', 'longitude']);
            $table->index('enable_alerts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
