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
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('report_id')->constrained('custom_reports')->onDelete('cascade');
            $table->string('name');
            $table->string('frequency'); // daily, weekly, monthly
            $table->string('format'); // pdf, csv, excel
            $table->json('recipients'); // Email addresses
            $table->string('day_of_week')->nullable(); // For weekly reports
            $table->integer('day_of_month')->nullable(); // For monthly reports
            $table->time('time_of_day')->default('09:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index('next_run_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
