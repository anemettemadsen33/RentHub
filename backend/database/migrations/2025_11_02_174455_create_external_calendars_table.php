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
        Schema::create('external_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('platform', 50); // 'airbnb', 'booking_com', 'vrbo', 'ical', 'google'
            $table->text('url')->nullable(); // iCal URL
            $table->string('name')->nullable(); // User-friendly name
            $table->boolean('sync_enabled')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('sync_error')->nullable(); // Last sync error if any
            $table->timestamps();
            
            $table->index('property_id');
            $table->index(['property_id', 'sync_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_calendars');
    }
};
