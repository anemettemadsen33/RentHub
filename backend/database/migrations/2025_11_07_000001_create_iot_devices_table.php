<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iot_device_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Thermostat, Light, Camera, Appliance
            $table->string('slug')->unique();
            $table->json('capabilities')->nullable(); // What the device can do
            $table->json('default_config')->nullable();
            $table->timestamps();
        });

        Schema::create('iot_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('iot_device_type_id')->constrained()->onDelete('cascade');
            $table->string('device_name');
            $table->string('device_id')->unique(); // Unique device identifier
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('location_in_property'); // Living Room, Bedroom, etc.
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline');
            $table->json('current_state')->nullable(); // Current temperature, light status, etc.
            $table->json('configuration')->nullable(); // Device-specific settings
            $table->timestamp('last_communication')->nullable();
            $table->boolean('guest_accessible')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'is_active']);
            $table->index('status');
        });

        Schema::create('iot_device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iot_device_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('command_type'); // set_temperature, turn_on, turn_off, etc.
            $table->json('command_params')->nullable();
            $table->enum('status', ['pending', 'sent', 'executed', 'failed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['iot_device_id', 'status']);
            $table->index('created_at');
        });

        Schema::create('iot_device_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iot_device_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // state_change, error, maintenance, etc.
            $table->json('event_data')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('event_timestamp');
            $table->timestamps();

            $table->index(['iot_device_id', 'event_timestamp']);
            $table->index('event_type');
        });

        Schema::create('iot_automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('rule_name');
            $table->text('description')->nullable();
            $table->json('trigger_conditions'); // When to execute
            $table->json('actions'); // What to do
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_executed')->nullable();
            $table->integer('execution_count')->default(0);
            $table->timestamps();

            $table->index(['property_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_automation_rules');
        Schema::dropIfExists('iot_device_logs');
        Schema::dropIfExists('iot_device_commands');
        Schema::dropIfExists('iot_devices');
        Schema::dropIfExists('iot_device_types');
    }
};
