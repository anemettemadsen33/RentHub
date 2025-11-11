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
            // Add import fields only if they don't exist
            if (! Schema::hasColumn('properties', 'imported_from')) {
                $table->string('imported_from')->nullable()->after('main_image'); // booking, airbnb, vrbo
                $table->index('imported_from');
            }
            if (! Schema::hasColumn('properties', 'external_id')) {
                $table->string('external_id')->nullable()->after('main_image'); // external platform property ID
                $table->index('external_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'imported_from')) {
                $table->dropIndex(['imported_from']);
                $table->dropColumn('imported_from');
            }
            if (Schema::hasColumn('properties', 'external_id')) {
                $table->dropIndex(['external_id']);
                $table->dropColumn('external_id');
            }
        });
    }
};
