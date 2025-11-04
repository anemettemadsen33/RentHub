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
        Schema::create('channel_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_connection_id')->constrained()->onDelete('cascade');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('sync_type'); // calendar, pricing, availability, listing
            $table->string('direction'); // push, pull, bidirectional
            $table->string('status'); // success, failed, partial
            $table->integer('items_synced')->default(0);
            $table->integer('items_failed')->default(0);
            $table->json('details')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['channel_connection_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_sync_logs');
    }
};
