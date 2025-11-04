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
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Silver, Gold, Platinum
            $table->string('slug')->unique(); // silver, gold, platinum
            $table->integer('min_points')->default(0);
            $table->integer('max_points')->nullable(); // null = unlimited
            $table->decimal('discount_percentage', 5, 2)->default(0); // e.g., 10.00 = 10%
            $table->decimal('points_multiplier', 3, 2)->default(1.00); // 1.5x, 2.0x
            $table->boolean('priority_booking')->default(false);
            $table->json('benefits')->nullable(); // Array of benefits
            $table->string('badge_color', 7)->default('#cccccc'); // Hex color
            $table->string('icon')->nullable(); // Icon name or emoji
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_tiers');
    }
};
