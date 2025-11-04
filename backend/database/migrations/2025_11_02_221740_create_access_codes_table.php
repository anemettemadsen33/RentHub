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
        Schema::create('access_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_lock_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Guest
            $table->string('code', 20); // Access PIN/code
            $table->string('external_code_id')->nullable(); // Provider's code ID
            $table->enum('type', ['temporary', 'permanent', 'one_time'])->default('temporary');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'revoked'])->default('pending');
            $table->integer('max_uses')->nullable(); // For one-time codes
            $table->integer('uses_count')->default(0);
            $table->boolean('notified')->default(false); // Guest notified
            $table->timestamp('notified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['smart_lock_id', 'status']);
            $table->index(['booking_id', 'status']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_codes');
    }
};
