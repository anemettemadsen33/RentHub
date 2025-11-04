<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 3)->unique();
                $table->string('name');
                $table->string('symbol', 10);
                $table->decimal('exchange_rate', 10, 6)->default(1);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });

            // Insert default currencies
            DB::table('currencies')->insert([
                ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 1, 'is_default' => true],
                ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 0.85],
                ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'exchange_rate' => 0.73],
                ['code' => 'RON', 'name' => 'Romanian Leu', 'symbol' => 'lei', 'exchange_rate' => 4.5],
            ]);
        }

        // Add currency support to properties
        if (Schema::hasTable('properties') && !Schema::hasColumn('properties', 'currency_code')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->string('currency_code', 3)->default('USD')->after('price_per_night');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('currencies');
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('currency_code');
        });
    }
};