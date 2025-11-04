<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Properties table indexes
        Schema::table('properties', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->fullText(['title', 'description']);
        });

        // Bookings table indexes
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('status');
            $table->index('check_in');
            $table->index('check_out');
            $table->index(['property_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['check_in', 'check_out']);
        });

        // Reviews table indexes
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('rating');
            $table->index('created_at');
            $table->index(['property_id', 'rating']);
            $table->index(['user_id', 'created_at']);
            $table->fullText('comment');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['email', 'email_verified_at']);
        });

        // Messages table indexes
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index('created_at');
                $table->index(['sender_id', 'created_at']);
                $table->index(['receiver_id', 'created_at']);
                $table->index(['conversation_id', 'created_at']);
            });
        }

        // Property amenities pivot table indexes
        if (Schema::hasTable('amenity_property')) {
            Schema::table('amenity_property', function (Blueprint $table) {
                $table->index('property_id');
                $table->index('amenity_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropFullText(['title', 'description']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['check_in']);
            $table->dropIndex(['check_out']);
            $table->dropIndex(['property_id', 'status']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['check_in', 'check_out']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['rating']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['property_id', 'rating']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropFullText(['comment']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['email', 'email_verified_at']);
        });

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
                $table->dropIndex(['sender_id', 'created_at']);
                $table->dropIndex(['receiver_id', 'created_at']);
                $table->dropIndex(['conversation_id', 'created_at']);
            });
        }

        if (Schema::hasTable('amenity_property')) {
            Schema::table('amenity_property', function (Blueprint $table) {
                $table->dropIndex(['property_id']);
                $table->dropIndex(['amenity_id']);
            });
        }
    }
};
