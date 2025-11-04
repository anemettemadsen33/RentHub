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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('long_term_rental_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            // Request Details
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['plumbing', 'electrical', 'hvac', 'appliance', 'structural', 'pest_control', 'cleaning', 'other'])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Status
            $table->enum('status', ['submitted', 'acknowledged', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('submitted');

            // Scheduling
            $table->timestamp('preferred_date')->nullable();
            $table->timestamp('scheduled_date')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Access
            $table->boolean('requires_access')->default(true);
            $table->text('access_instructions')->nullable();

            // Photos & Documents
            $table->json('photos')->nullable();
            $table->json('documents')->nullable();

            // Cost
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->enum('payment_responsibility', ['owner', 'tenant', 'shared', 'insurance'])->default('owner');

            // Resolution
            $table->text('resolution_notes')->nullable();
            $table->json('completion_photos')->nullable();

            // Ratings
            $table->integer('tenant_rating')->nullable();
            $table->text('tenant_feedback')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
