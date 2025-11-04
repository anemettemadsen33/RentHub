<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // GDPR Consent tracking
            $table->timestamp('terms_accepted_at')->nullable()->after('remember_token');
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->boolean('data_processing_consent')->default(false);
            $table->timestamp('consent_updated_at')->nullable();
            
            // Account deletion tracking
            $table->timestamp('deletion_requested_at')->nullable();
            $table->text('deletion_reason')->nullable();
            
            // Activity tracking
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'terms_accepted_at',
                'privacy_accepted_at',
                'marketing_consent',
                'data_processing_consent',
                'consent_updated_at',
                'deletion_requested_at',
                'deletion_reason',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
