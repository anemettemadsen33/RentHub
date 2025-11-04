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
            $table->integer('square_footage')->nullable()->after('area_sqm');
            $table->enum('furnishing_status', ['furnished', 'semi_furnished', 'unfurnished'])->default('unfurnished')->after('type');
            $table->integer('floor_number')->nullable()->after('built_year');
            $table->boolean('parking_available')->default(false)->after('floor_number');
            $table->integer('parking_spaces')->nullable()->after('parking_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'square_footage',
                'furnishing_status',
                'floor_number',
                'parking_available',
                'parking_spaces',
            ]);
        });
    }
};
