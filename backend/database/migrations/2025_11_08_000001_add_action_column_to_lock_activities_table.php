<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lock_activities') && ! Schema::hasColumn('lock_activities', 'action')) {
            Schema::table('lock_activities', function (Blueprint $table) {
                $table->string('action')->nullable()->after('event_type');
                $table->index('action');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lock_activities') && Schema::hasColumn('lock_activities', 'action')) {
            Schema::table('lock_activities', function (Blueprint $table) {
                $table->dropIndex(['action']);
                $table->dropColumn('action');
            });
        }
    }
};
