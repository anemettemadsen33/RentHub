<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This table is also created by 2025_01_03_000001_create_security_tables.php
        // Skip creation if it already exists
        
        if (!Schema::hasTable('oauth_clients')) {
            Schema::create('oauth_clients', function (Blueprint $table) {
                $table->id();
                $table->string('client_id')->unique();
                $table->string('client_secret');
                $table->string('name');
                $table->json('redirect_uris');
                $table->json('scopes')->nullable();
                $table->boolean('is_confidential')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_clients');
    }
};
