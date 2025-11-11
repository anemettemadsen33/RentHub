<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saved_searches', function (Blueprint $table) {
            if (!Schema::hasColumn('saved_searches', 'filters')) {
                $table->json('filters')->nullable()->after('amenities');
            }
            if (!Schema::hasColumn('saved_searches', 'notify')) {
                $table->boolean('notify')->default(false)->after('enable_alerts');
            }
            if (!Schema::hasColumn('saved_searches', 'last_executed_at')) {
                $table->timestamp('last_executed_at')->nullable()->after('last_searched_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('saved_searches', function (Blueprint $table) {
            if (Schema::hasColumn('saved_searches', 'filters')) {
                $table->dropColumn('filters');
            }
            if (Schema::hasColumn('saved_searches', 'notify')) {
                $table->dropColumn('notify');
            }
            if (Schema::hasColumn('saved_searches', 'last_executed_at')) {
                $table->dropColumn('last_executed_at');
            }
        });
    }
};
