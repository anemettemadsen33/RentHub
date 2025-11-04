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
        Schema::create('channel_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_connection_id')->constrained()->onDelete('cascade');
            $table->string('channel'); // airbnb, booking_com, vrbo
            $table->string('external_id'); // Listing ID on the channel
            $table->string('listing_url')->nullable();
            $table->string('status')->default('active'); // active, inactive, paused
            $table->json('mapping')->nullable(); // Field mappings
            $table->boolean('sync_calendar')->default(true);
            $table->boolean('sync_pricing')->default(true);
            $table->boolean('sync_availability')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            $table->unique(['property_id', 'channel']);
            $table->index('external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_listings');
    }
};
