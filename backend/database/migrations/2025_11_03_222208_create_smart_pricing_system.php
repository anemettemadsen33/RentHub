<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pricing_rules')) {
            Schema::create('pricing_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->onDelete('cascade');
                $table->enum('rule_type', ['seasonal', 'weekend', 'holiday', 'last_minute', 'early_bird']);
                $table->string('name');
                $table->decimal('adjustment_value', 8, 2);
                $table->enum('adjustment_type', ['percentage', 'fixed']);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->json('days_of_week')->nullable();
                $table->integer('min_nights')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('price_history')) {
            Schema::create('price_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->onDelete('cascade');
                $table->date('date');
                $table->decimal('base_price', 10, 2);
                $table->decimal('final_price', 10, 2);
                $table->json('applied_rules')->nullable();
                $table->timestamps();
                
                $table->unique(['property_id', 'date']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('price_history');
        Schema::dropIfExists('pricing_rules');
    }
};