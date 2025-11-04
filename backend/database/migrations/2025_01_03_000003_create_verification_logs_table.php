<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_verification_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Admin who performed action
            
            $table->string('verification_type'); // identity, credit, background, reference
            $table->enum('action', ['submitted', 'approved', 'rejected', 'expired', 'updated']);
            $table->text('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            $table->index('verification_type');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};
