<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This table is also created by earlier migrations
        // Skip creation if it already exists

        if (! Schema::hasTable('security_incidents')) {
            Schema::create('security_incidents', function (Blueprint $table) {
                $table->id();
                $table->string('type', 50)->index();
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->index();
                $table->enum('status', ['open', 'investigating', 'escalated', 'resolved', 'closed'])->default('open')->index();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->string('ip_address', 45)->index();
                $table->text('user_agent')->nullable();
                $table->string('url')->nullable();
                $table->string('method', 10)->nullable();
                $table->json('details')->nullable();
                $table->timestamp('detected_at')->index();
                $table->timestamp('escalated_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->text('resolution_notes')->nullable();
                $table->timestamps();

                $table->index(['type', 'severity']);
                $table->index(['status', 'detected_at']);
            });

            // Create index for fast querying of open critical incidents
            Schema::table('security_incidents', function (Blueprint $table) {
                $table->index(['severity', 'status', 'detected_at'], 'idx_critical_open');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('security_incidents');
    }
};
