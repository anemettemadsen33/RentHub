<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Security audit logs - skip if already exists (created by 2025_01_03_000001_create_security_tables.php)
        // This table is already created by an earlier migration

        // API keys management - skip if already exists (created by 2024_11_03_000003_create_api_keys_table.php)
        // This table is already created by an earlier migration

        // Session management
        if (! Schema::hasTable('active_sessions')) {
            Schema::create('active_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('session_id')->unique();
                $table->string('ip_address');
                $table->string('user_agent');
                $table->string('device_type')->nullable();
                $table->timestamp('last_activity');
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->index(['user_id', 'expires_at']);
            });
        }

        // Data requests (GDPR)
        if (! Schema::hasTable('data_requests')) {
            Schema::create('data_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['export', 'deletion', 'rectification']);
                $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamp('requested_at');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['type', 'status']);
            });
        }

        // Security incidents - skip if already exists (created by 2025_01_03_000001_create_security_tables.php)
        // This table is already created by an earlier migration

        // Failed login attempts
        if (! Schema::hasTable('failed_login_attempts')) {
            Schema::create('failed_login_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('email')->index();
                $table->string('ip_address')->index();
                $table->string('user_agent')->nullable();
                $table->timestamp('attempted_at');

                $table->index(['email', 'attempted_at']);
                $table->index(['ip_address', 'attempted_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_login_attempts');
        Schema::dropIfExists('security_incidents');
        Schema::dropIfExists('data_requests');
        Schema::dropIfExists('active_sessions');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('security_audit_logs');
    }
};
