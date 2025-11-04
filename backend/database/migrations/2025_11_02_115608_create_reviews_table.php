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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // reviewer
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');

            // Review content
            $table->integer('rating'); // 1-5 stars
            $table->text('comment')->nullable();

            // Detailed ratings
            $table->integer('cleanliness_rating')->nullable(); // 1-5
            $table->integer('communication_rating')->nullable(); // 1-5
            $table->integer('check_in_rating')->nullable(); // 1-5
            $table->integer('accuracy_rating')->nullable(); // 1-5
            $table->integer('location_rating')->nullable(); // 1-5
            $table->integer('value_rating')->nullable(); // 1-5

            // Photos
            $table->json('photos')->nullable(); // Array of photo URLs

            // Helpful votes
            $table->integer('helpful_count')->default(0);

            // Moderation
            $table->boolean('is_approved')->default(true);
            $table->text('admin_notes')->nullable();

            // Response from owner
            $table->text('owner_response')->nullable();
            $table->timestamp('owner_response_at')->nullable();

            $table->timestamps();

            // Constraints - one review per booking per user
            $table->unique(['booking_id', 'user_id']);

            // Indexes
            $table->index(['property_id', 'is_approved']);
            $table->index(['rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
