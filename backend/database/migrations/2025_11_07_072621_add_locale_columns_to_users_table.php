<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'language')) {
                $table->string('language', 10)->default('en')->after('country');
            }
            if (! Schema::hasColumn('users', 'currency')) {
                $table->string('currency', 10)->default('USD')->after('language');
            }
            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 64)->default('UTC')->after('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $drops = [];
            foreach (['language', 'currency', 'timezone'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $drops[] = $col;
                }
            }
            if ($drops) {
                $table->dropColumn($drops);
            }
        });
    }
};
