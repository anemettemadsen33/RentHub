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
            // Fix status enum: change from draft/published/inactive to available/booked/maintenance
            // Drop old enum and recreate with correct values
            $table->dropColumn('status');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->enum('status', ['available', 'booked', 'maintenance'])
                ->default('available')
                ->after('is_active');
        });

        Schema::table('properties', function (Blueprint $table) {
            // Add location column for full address string (convenience field)
            $table->string('location')->nullable()->after('postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('location');
            $table->dropColumn('status');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'inactive'])
                ->default('draft')
                ->after('is_active');
        });
    }
};
