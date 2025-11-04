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
        Schema::create('concierge_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Guest booking
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null'); // Related property
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null'); // Related property booking
            $table->foreignId('concierge_service_id')->constrained()->onDelete('cascade');
            $table->string('booking_reference')->unique();

            // Service details
            $table->dateTime('service_date');
            $table->time('service_time')->nullable();
            $table->integer('guests_count')->default(1);
            $table->text('special_requests')->nullable();

            // Pricing
            $table->decimal('base_price', 10, 2);
            $table->decimal('extras_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'in_progress',
                'completed',
                'cancelled',
                'refunded',
            ])->default('pending');

            // Payment
            $table->enum('payment_status', [
                'pending',
                'paid',
                'refunded',
                'failed',
            ])->default('pending');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');

            // Service execution
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Rating & Review
            $table->integer('rating')->nullable();
            $table->text('review')->nullable();
            $table->dateTime('reviewed_at')->nullable();

            // Contact info
            $table->json('contact_details')->nullable(); // Phone, address for service

            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('property_id');
            $table->index('booking_id');
            $table->index('concierge_service_id');
            $table->index('status');
            $table->index('service_date');
            $table->index('booking_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concierge_bookings');
    }
};
