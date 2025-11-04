<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oauth_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('access_token', 128)->unique();
            $table->string('refresh_token', 128)->unique();
            $table->json('scopes')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('refresh_expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('revoked')->default(false);
            $table->string('client_id')->nullable();
            $table->string('client_name')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'revoked']);
            $table->index('expires_at');
            $table->index('refresh_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_tokens');
    }
};
