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
        Schema::table('properties', function (Blueprint $table) {
            // Pricing options
            $table->decimal('price_per_week', 10, 2)->nullable()->after('price_per_night');
            $table->decimal('price_per_month', 10, 2)->nullable()->after('price_per_week');
            
            // Property rules
            $table->json('rules')->nullable()->after('description');
            
            // Property status
            $table->enum('status', ['draft', 'published', 'inactive'])->default('draft')->after('is_active');
            
            // Availability calendar
            $table->json('blocked_dates')->nullable()->after('available_until');
            $table->json('custom_pricing')->nullable()->after('blocked_dates');
            
            // Additional info
            $table->integer('min_nights')->default(1)->after('guests');
            $table->integer('max_nights')->nullable()->after('min_nights');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'price_per_week',
                'price_per_month',
                'rules',
                'status',
                'blocked_dates',
                'custom_pricing',
                'min_nights',
                'max_nights',
            ]);
        });
    }
};
