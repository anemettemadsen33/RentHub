<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analytics_event_archives')) {
            Schema::create('analytics_event_archives', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->date('day');
                $table->string('type');
                $table->unsignedBigInteger('count');
                $table->timestamps();
                $table->unique(['day', 'type']);
                $table->index(['day', 'type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_event_archives');
    }
};
