<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_processing_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('consent_type', ['gdpr', 'ccpa', 'marketing', 'analytics'])->default('gdpr');
            $table->json('categories')->nullable();
            $table->string('purpose')->nullable();
            $table->boolean('do_not_sell')->default(false);
            $table->timestamp('consented_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('consent_text')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'consent_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_processing_consents');
    }
};
