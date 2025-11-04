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
        Schema::create('cleaning_schedules', function (Blueprint $table) {
            $table->id();
            
            // References
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_provider_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Schedule Type
            $table->enum('schedule_type', ['recurring', 'one_time'])->default('recurring');
            
            // Recurring Settings
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'custom'])->nullable();
            $table->json('days_of_week')->nullable(); // [1,3,5] for Mon, Wed, Fri
            $table->integer('day_of_month')->nullable(); // For monthly: 1-31
            $table->json('custom_schedule')->nullable(); // For complex schedules
            
            // Time
            $table->time('preferred_time');
            $table->integer('duration_hours')->default(2);
            
            // Service Details
            $table->enum('service_type', [
                'regular_cleaning',
                'deep_cleaning',
                'turnover',
                'inspection',
                'custom'
            ])->default('regular_cleaning');
            
            $table->json('cleaning_checklist')->nullable();
            $table->text('special_instructions')->nullable();
            
            // Date Range
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Null = indefinite
            
            // Status
            $table->boolean('active')->default(true);
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamp('next_execution_at')->nullable();
            
            // Auto-booking
            $table->boolean('auto_book')->default(true); // Automatically create CleaningService records
            $table->integer('book_days_in_advance')->default(7); // How many days before to book
            
            // Notifications
            $table->boolean('notify_provider')->default(true);
            $table->boolean('notify_owner')->default(true);
            $table->integer('reminder_hours_before')->default(24);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['property_id', 'active']);
            $table->index('next_execution_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_schedules');
    }
};
