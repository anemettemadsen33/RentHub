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
        Schema::create('custom_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('report_type'); // revenue, bookings, occupancy, performance, custom
            $table->text('description')->nullable();
            $table->json('filters')->nullable(); // Date range, properties, categories
            $table->json('columns')->nullable(); // Selected columns to display
            $table->json('grouping')->nullable(); // Group by options
            $table->json('sorting')->nullable(); // Sort options
            $table->json('chart_config')->nullable(); // Visualization settings
            $table->boolean('is_public')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->integer('run_count')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'report_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_reports');
    }
};
