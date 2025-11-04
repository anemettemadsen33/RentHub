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
        // Only modify table if it exists
        if (Schema::hasTable('service_providers')) {
            Schema::table('service_providers', function (Blueprint $table) {
                // Add concierge service types to existing enum if not already there
                // Note: In SQLite this might need recreation, but for most DBs we just add new types
                if (! Schema::hasColumn('service_providers', 'concierge_services')) {
                    $table->json('concierge_services')->nullable()->after('service_areas'); // Array of concierge service types they offer
                }
                if (Schema::hasColumn('service_providers', 'rating')) {
                    $table->decimal('rating', 3, 2)->default(0)->after('concierge_services')->change();
                }
                if (Schema::hasColumn('service_providers', 'total_bookings')) {
                    $table->integer('total_bookings')->default(0)->after('rating')->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn('concierge_services');
        });
    }
};
