<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // OAuth Clients - skip if already exists (created by other migration)
        if (! Schema::hasTable('oauth_clients')) {
            Schema::create('oauth_clients', function (Blueprint $table) {
                $table->id();
                $table->string('client_id')->unique();
                $table->string('client_secret');
                $table->string('name');
                $table->string('redirect_uri');
                $table->boolean('is_confidential')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // OAuth Access Tokens
        if (! Schema::hasTable('oauth_access_tokens')) {
            Schema::create('oauth_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('client_id')->constrained('oauth_clients')->onDelete('cascade');
                $table->string('token', 100)->unique();
                $table->json('scopes')->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->index(['token', 'expires_at']);
            });
        }

        // OAuth Refresh Tokens
        if (! Schema::hasTable('oauth_refresh_tokens')) {
            Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('client_id')->constrained('oauth_clients')->onDelete('cascade');
                $table->string('token', 100)->unique();
                $table->json('scopes')->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->index(['token', 'expires_at']);
            });
        }

        // API Keys - skip if already exists (created by 2024_11_03_000003_create_api_keys_table.php)
        // This table is already created by an earlier migration

        // Roles - skip if already exists (created by 2024_11_03_000001_create_roles_permissions_tables.php)
        // This table is already created by an earlier migration

        // Permissions - skip if already exists (created by 2024_11_03_000001_create_roles_permissions_tables.php)
        // This table is already created by an earlier migration

        // Role-User Pivot - skip if already exists
        // This table is already created by an earlier migration

        // Permission-Role Pivot - skip if already exists
        // This table is already created by an earlier migration

        // Permission-User Pivot (direct permissions) - skip if already exists
        // This table is already created by an earlier migration

        // Security Audit Logs
        if (! Schema::hasTable('security_audit_logs')) {
            Schema::create('security_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('category'); // authentication, authorization, data_access, etc.
                $table->string('event');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->boolean('successful')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamp('created_at');

                $table->index(['user_id', 'created_at']);
                $table->index(['category', 'created_at']);
            });
        }

        // Security Incidents
        if (! Schema::hasTable('security_incidents')) {
            Schema::create('security_incidents', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->enum('severity', ['low', 'medium', 'high', 'critical']);
                $table->text('description');
                $table->json('metadata')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->enum('status', ['open', 'investigating', 'resolved', 'false_positive'])->default('open');
                $table->timestamp('detected_at');
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'severity']);
            });
        }

        // GDPR Requests
        if (! Schema::hasTable('gdpr_requests')) {
            Schema::create('gdpr_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['export', 'deletion', 'rectification']);
                $table->enum('status', ['pending', 'processing', 'completed', 'failed']);
                $table->string('file_path')->nullable();
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'type', 'status']);
            });
        }

        // Data Consents
        if (! Schema::hasTable('data_consents')) {
            Schema::create('data_consents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('consent_type'); // marketing, analytics, etc.
                $table->boolean('granted')->default(false);
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->json('details')->nullable();
                $table->timestamp('granted_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'consent_type']);
            });
        }

        // Password History (for preventing reuse)
        if (! Schema::hasTable('password_histories')) {
            Schema::create('password_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('password');
                $table->timestamps();

                $table->index('user_id');
            });
        }

        // Login Attempts
        if (! Schema::hasTable('login_attempts')) {
            Schema::create('login_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('ip_address');
                $table->boolean('successful')->default(false);
                $table->text('user_agent')->nullable();
                $table->timestamp('attempted_at');

                $table->index(['email', 'attempted_at']);
                $table->index(['ip_address', 'attempted_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
        Schema::dropIfExists('password_histories');
        Schema::dropIfExists('data_consents');
        Schema::dropIfExists('gdpr_requests');
        Schema::dropIfExists('security_incidents');
        Schema::dropIfExists('security_audit_logs');
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_clients');
    }
};
