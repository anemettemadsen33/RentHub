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
            $table->enum('id_type', ['passport', 'drivers_license', 'national_id'])->nullable()->after('government_id_verified_at');
            $table->string('id_number', 100)->nullable()->after('id_type');
            $table->string('id_front_image')->nullable()->after('id_number');
            $table->string('id_back_image')->nullable()->after('id_front_image');
            $table->enum('id_verification_status', ['not_submitted', 'pending', 'approved', 'rejected'])->default('not_submitted')->after('id_back_image');
            $table->text('id_rejection_reason')->nullable()->after('id_verification_status');
            $table->timestamp('id_submitted_at')->nullable()->after('id_rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'id_type',
                'id_number',
                'id_front_image',
                'id_back_image',
                'id_verification_status',
                'id_rejection_reason',
                'id_submitted_at',
            ]);
        });
    }
};
