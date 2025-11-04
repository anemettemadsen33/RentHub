<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable()->after('password');
            $table->timestamp('locked_until')->nullable()->after('email_verified_at');
            $table->integer('failed_login_attempts')->default(0)->after('locked_until');
            $table->timestamp('deletion_scheduled_at')->nullable()->after('failed_login_attempts');
            $table->boolean('two_factor_enabled')->default(false)->after('deletion_scheduled_at');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            $table->string('device_fingerprint')->nullable()->after('two_factor_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'password_changed_at',
                'locked_until',
                'failed_login_attempts',
                'deletion_scheduled_at',
                'two_factor_enabled',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'device_fingerprint',
            ]);
        });
    }
};
