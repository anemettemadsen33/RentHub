<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_preferences', 'email_enabled')) {
                $table->boolean('email_enabled')->default(true)->after('channel_push');
            }
            if (!Schema::hasColumn('notification_preferences', 'sms_enabled')) {
                $table->boolean('sms_enabled')->default(false)->after('email_enabled');
            }
            if (!Schema::hasColumn('notification_preferences', 'push_enabled')) {
                $table->boolean('push_enabled')->default(false)->after('sms_enabled');
            }
            if (!Schema::hasColumn('notification_preferences', 'booking_updates')) {
                $table->boolean('booking_updates')->default(true)->after('push_enabled');
            }
            if (!Schema::hasColumn('notification_preferences', 'payment_updates')) {
                $table->boolean('payment_updates')->default(true)->after('booking_updates');
            }
            if (!Schema::hasColumn('notification_preferences', 'message_updates')) {
                $table->boolean('message_updates')->default(true)->after('payment_updates');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            foreach ([
                'email_enabled','sms_enabled','push_enabled','booking_updates','payment_updates','message_updates'
            ] as $col) {
                if (Schema::hasColumn('notification_preferences', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
