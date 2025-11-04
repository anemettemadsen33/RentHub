<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('translatable_type');
                $table->unsignedBigInteger('translatable_id');
                $table->string('locale', 5);
                $table->string('field');
                $table->text('value');
                $table->timestamps();
                
                $table->index(['translatable_type', 'translatable_id']);
                $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'translations_unique');
            });
        }

        // Add supported languages to settings
        if (!Schema::hasTable('language_settings')) {
            Schema::create('language_settings', function (Blueprint $table) {
                $table->id();
                $table->string('code', 5)->unique();
                $table->string('name');
                $table->string('native_name');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_rtl')->default(false);
                $table->timestamps();
            });

            // Insert default languages
            DB::table('language_settings')->insert([
                ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => true, 'is_rtl' => false],
                ['code' => 'ro', 'name' => 'Romanian', 'native_name' => 'Română', 'is_default' => false, 'is_rtl' => false],
                ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'is_default' => false, 'is_rtl' => false],
                ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'is_default' => false, 'is_rtl' => false],
                ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'is_default' => false, 'is_rtl' => false],
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('language_settings');
    }
};