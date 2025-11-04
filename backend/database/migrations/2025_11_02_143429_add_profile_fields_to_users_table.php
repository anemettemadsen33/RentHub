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
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('bio');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->string('address', 500)->nullable()->after('gender');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('country', 100)->nullable()->after('state');
            $table->string('zip_code', 20)->nullable()->after('country');
            
            // Settings & Privacy
            $table->json('settings')->nullable()->after('profile_completed_at');
            $table->json('privacy_settings')->nullable()->after('settings');
            
            // Verification badges
            $table->timestamp('identity_verified_at')->nullable()->after('phone_verified_at');
            $table->timestamp('government_id_verified_at')->nullable()->after('identity_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'gender',
                'address',
                'city',
                'state',
                'country',
                'zip_code',
                'settings',
                'privacy_settings',
                'identity_verified_at',
                'government_id_verified_at',
            ]);
        });
    }
};
