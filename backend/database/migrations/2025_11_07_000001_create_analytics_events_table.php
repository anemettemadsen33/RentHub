<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->json('payload')->nullable();
            $table->timestamp('event_timestamp')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_role')->nullable();
            $table->string('client_id')->nullable()->index();
            $table->timestamps();
            $table->index(['type', 'event_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
