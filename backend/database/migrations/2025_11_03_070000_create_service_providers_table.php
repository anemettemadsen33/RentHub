<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->enum('service_type', ['cleaning', 'maintenance', 'concierge', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->json('service_areas')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_bookings')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};
