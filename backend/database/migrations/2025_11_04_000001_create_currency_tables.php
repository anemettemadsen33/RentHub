<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 3)->unique();
                $table->string('symbol', 10);
                $table->string('name');
                $table->integer('decimal_places')->default(2);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('exchange_rates')) {
            Schema::create('exchange_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('from_currency_id')->constrained('currencies')->onDelete('cascade');
                $table->foreignId('to_currency_id')->constrained('currencies')->onDelete('cascade');
                $table->decimal('rate', 20, 10);
                $table->string('source')->default('manual');
                $table->timestamps();

                $table->index(['from_currency_id', 'to_currency_id']);
                $table->unique(['from_currency_id', 'to_currency_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
    }
};
