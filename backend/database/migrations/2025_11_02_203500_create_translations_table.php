<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10)->index(); // en, ro, es, fr, de, ar, he
            $table->string('group')->index(); // common, properties, bookings, etc.
            $table->string('key')->index(); // welcome_message, property_title, etc.
            $table->text('value');
            $table->timestamps();
            
            // Unique: one translation per locale+group+key
            $table->unique(['locale', 'group', 'key']);
        });

        Schema::create('supported_languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // en, ro, es, fr, de, ar, he
            $table->string('name'); // English, Română, Español
            $table->string('native_name'); // English, Română, Español
            $table->boolean('is_rtl')->default(false); // true for ar, he
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default languages
        DB::table('supported_languages')->insert([
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_rtl' => false, 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ro', 'name' => 'Romanian', 'native_name' => 'Română', 'is_rtl' => false, 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'is_rtl' => false, 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'is_rtl' => false, 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'is_rtl' => false, 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'is_rtl' => true, 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'he', 'name' => 'Hebrew', 'native_name' => 'עברית', 'is_rtl' => true, 'is_active' => true, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('supported_languages');
    }
};
