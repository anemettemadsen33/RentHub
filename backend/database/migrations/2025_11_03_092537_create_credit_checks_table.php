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
        Schema::create('credit_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_screening_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');

            // Provider information
            $table->string('provider')->nullable(); // equifax, experian, transunion, etc
            $table->string('provider_reference')->nullable();

            // Credit score
            $table->integer('credit_score')->nullable();
            $table->integer('max_score')->default(850);
            $table->enum('credit_rating', ['excellent', 'good', 'fair', 'poor', 'very_poor'])->nullable();

            // Credit report data
            $table->json('report_data')->nullable();
            $table->integer('total_accounts')->nullable();
            $table->integer('open_accounts')->nullable();
            $table->decimal('total_debt', 12, 2)->nullable();
            $table->decimal('available_credit', 12, 2)->nullable();
            $table->decimal('credit_utilization', 5, 2)->nullable(); // percentage

            // Payment history
            $table->integer('on_time_payments')->nullable();
            $table->integer('late_payments')->nullable();
            $table->integer('missed_payments')->nullable();
            $table->integer('defaults')->nullable();
            $table->integer('bankruptcies')->nullable();

            // Status
            $table->enum('status', ['pending', 'completed', 'failed', 'expired'])->default('pending');
            $table->boolean('passed')->nullable();
            $table->text('failure_reason')->nullable();

            // Metadata
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('guest_screening_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('credit_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_checks');
    }
};
