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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // en, ro, fr, de, es
            $table->string('name'); // English, Romanian, French
            $table->string('native_name'); // English, Română, Français
            $table->string('flag_emoji')->nullable(); // 🇬🇧, 🇷🇴, 🇫🇷
            $table->boolean('is_rtl')->default(false); // Right-to-left (Arabic, Hebrew)
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
        Schema::dropIfExists('languages');
    }
};
