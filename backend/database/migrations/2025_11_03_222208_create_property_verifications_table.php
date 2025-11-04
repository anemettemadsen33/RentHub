<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('property_verifications')) {
            Schema::create('property_verifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained()->onDelete('cascade');
                $table->enum('verification_type', ['ownership', 'inspection', 'documents']);
                $table->enum('status', ['pending', 'in_review', 'approved', 'rejected'])->default('pending');
                $table->json('documents')->nullable();
                $table->text('notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users');
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
            });
        }

        // Add verification status to properties table
        if (Schema::hasTable('properties') && !Schema::hasColumn('properties', 'is_verified')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->boolean('is_verified')->default(false)->after('status');
                $table->timestamp('verified_at')->nullable()->after('is_verified');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('property_verifications');
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'verified_at']);
        });
    }
};