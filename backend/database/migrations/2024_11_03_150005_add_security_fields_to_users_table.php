<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ccpa_do_not_sell')->default(false)->after('email_verified_at');
            $table->timestamp('ccpa_opt_out_date')->nullable()->after('ccpa_do_not_sell');
            $table->boolean('gdpr_consent')->default(false)->after('ccpa_opt_out_date');
            $table->timestamp('gdpr_consent_date')->nullable()->after('gdpr_consent');
            $table->timestamp('password_changed_at')->nullable()->after('password');
            $table->json('password_history')->nullable()->after('password_changed_at');
            $table->integer('failed_login_attempts')->default(0)->after('password_history');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->string('device_fingerprint')->nullable()->after('locked_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ccpa_do_not_sell',
                'ccpa_opt_out_date',
                'gdpr_consent',
                'gdpr_consent_date',
                'password_changed_at',
                'password_history',
                'failed_login_attempts',
                'locked_until',
                'device_fingerprint',
            ]);
        });
    }
};
