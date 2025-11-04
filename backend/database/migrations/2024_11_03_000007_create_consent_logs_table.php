<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('consent_type'); // terms, privacy, marketing, data_processing
            $table->boolean('granted');
            $table->string('version'); // Policy version
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'consent_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_logs');
    }
};
