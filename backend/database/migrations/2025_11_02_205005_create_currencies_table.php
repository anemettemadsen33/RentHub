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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // USD, EUR, RON, GBP
            $table->string('name'); // US Dollar, Euro, Romanian Leu
            $table->string('symbol'); // $, €, lei, £
            $table->string('symbol_position')->default('before'); // before, after
            $table->integer('decimal_places')->default(2);
            $table->string('thousand_separator')->default(',');
            $table->string('decimal_separator')->default('.');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
