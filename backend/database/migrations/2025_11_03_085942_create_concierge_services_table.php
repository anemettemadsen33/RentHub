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
        Schema::create('concierge_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->enum('service_type', [
                'airport_pickup',
                'grocery_delivery',
                'local_experience',
                'local_experiences',
                'personal_chef',
                'spa_service',
                'spa_services',
                'car_rental',
                'babysitting',
                'housekeeping',
                'pet_care',
                'other',
            ]);
            $table->decimal('base_price', 10, 2);
            $table->string('price_unit')->default('per service'); // per hour, per service, per person
            $table->integer('duration_minutes')->nullable(); // Estimated duration
            $table->integer('max_guests')->nullable(); // Max people for service
            $table->json('pricing_extras')->nullable(); // Extra charges (airport distance, # of people, etc)
            $table->json('requirements')->nullable(); // Special requirements
            $table->json('images')->nullable();
            $table->boolean('is_available')->default(true);
            $table->integer('advance_booking_hours')->default(24); // Minimum hours in advance
            $table->timestamps();
            $table->softDeletes();

            $table->index('service_type');
            $table->index('service_provider_id');
            $table->index('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concierge_services');
    }
};
