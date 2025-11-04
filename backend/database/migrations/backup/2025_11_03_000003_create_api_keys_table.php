<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name')->comment('Friendly name for the API key');
            $table->string('key', 64)->unique()->comment('Hashed API key');
            $table->string('secret', 64)->nullable()->comment('Secret for request signing');
            $table->text('permissions')->nullable()->comment('JSON array of permissions');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->integer('request_count')->default(0);
            $table->json('rate_limits')->nullable()->comment('Custom rate limits');
            $table->timestamps();

            $table->index(['is_active', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
