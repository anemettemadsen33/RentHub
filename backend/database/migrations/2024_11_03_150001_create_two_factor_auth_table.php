<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('two_factor_auth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('method', ['totp', 'sms', 'email'])->default('totp');
            $table->text('secret')->nullable();
            $table->string('phone_number')->nullable();
            $table->json('backup_codes')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('two_factor_auth');
    }
};
