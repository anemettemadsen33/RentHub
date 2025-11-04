<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Security audit logs
        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('ip_address')->index();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->timestamp('created_at');
            
            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // API keys management
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('key')->unique();
            $table->text('permissions')->nullable();
            $table->integer('rate_limit')->default(60);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index('key');
        });

        // Session management
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

        // Data requests (GDPR)
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

        // Security incidents
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->text('description');
            $table->json('affected_systems')->nullable();
            $table->json('affected_users')->nullable();
            $table->enum('status', ['detected', 'investigating', 'contained', 'resolved'])->default('detected');
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'severity']);
            $table->index('status');
        });

        // Failed login attempts
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
