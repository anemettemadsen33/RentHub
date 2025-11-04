<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add spatial index for geo queries
        Schema::table('properties', function (Blueprint $table) {
            $table->index(['latitude', 'longitude'], 'properties_geo_index');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('properties_geo_index');
        });
    }
};
