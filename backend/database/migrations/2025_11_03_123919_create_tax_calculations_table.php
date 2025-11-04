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
        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('tax_type'); // vat, sales_tax, occupancy_tax, service_tax
            $table->string('tax_name');
            $table->decimal('rate', 5, 2); // Tax rate percentage
            $table->decimal('base_amount', 10, 2); // Amount before tax
            $table->decimal('tax_amount', 10, 2); // Calculated tax
            $table->decimal('total_amount', 10, 2); // Base + tax
            $table->string('jurisdiction')->nullable(); // Country, state, city
            $table->date('calculation_date');
            $table->json('breakdown')->nullable(); // Detailed calculation
            $table->timestamps();
            
            $table->index(['user_id', 'calculation_date']);
            $table->index('tax_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_calculations');
    }
};
