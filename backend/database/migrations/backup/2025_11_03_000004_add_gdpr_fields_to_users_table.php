<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('consent_marketing')->default(false);
            $table->boolean('consent_analytics')->default(false);
            $table->boolean('consent_data_processing')->default(true);
            $table->boolean('consent_third_party')->default(false);
            $table->timestamp('consents_updated_at')->nullable();
            $table->boolean('gdpr_forgotten')->default(false);
            $table->string('deletion_reason')->nullable();
            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'consent_marketing',
                'consent_analytics',
                'consent_data_processing',
                'consent_third_party',
                'consents_updated_at',
                'gdpr_forgotten',
                'deletion_reason',
                'last_login_at',
            ]);
        });
    }
};
