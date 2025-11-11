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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('type'); // apartment, house, villa, etc
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->integer('guests')->default(1);
            $table->decimal('price_per_night', 10, 2);
            // Backwards compatibility 'price' column expected by legacy tests
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('cleaning_fee', 10, 2)->nullable();
            $table->decimal('security_deposit', 10, 2)->nullable();

            // Address fields
            $table->string('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('postal_code');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Property details
            $table->integer('area_sqm')->nullable(); // area in square meters
            $table->year('built_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();

            // Images
            $table->json('images')->nullable(); // array of image paths
            $table->string('main_image')->nullable();

            // Owner
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // For some tests referencing owner_id explicitly
            $table->unsignedBigInteger('owner_id')->nullable()->index();

            $table->timestamps();

            // Indexes
            $table->index(['city', 'country']);
            $table->index(['price_per_night']);
            $table->index(['is_active', 'is_featured']);
            $table->index(['available_from', 'available_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
