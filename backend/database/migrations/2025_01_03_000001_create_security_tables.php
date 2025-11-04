<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // OAuth Clients
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

        // OAuth Access Tokens
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

        // OAuth Refresh Tokens
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

        // API Keys
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('key');
            $table->json('scopes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
        });

        // Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });

        // Role-User Pivot
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->primary(['role_id', 'user_id']);
        });

        // Permission-Role Pivot
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->primary(['permission_id', 'role_id']);
        });

        // Permission-User Pivot (direct permissions)
        Schema::create('permission_user', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->primary(['permission_id', 'user_id']);
        });

        // Security Audit Logs
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

        // Security Incidents
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

        // GDPR Requests
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

        // Data Consents
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

        // Password History (for preventing reuse)
        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('password');
            $table->timestamps();
            
            $table->index('user_id');
        });

        // Login Attempts
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
