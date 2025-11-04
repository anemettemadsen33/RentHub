<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Notifications\Account\WelcomeNotification;

echo "🧪 Testing RentHub Notification System...\n\n";

// Check if users exist
$user = User::first();

if (! $user) {
    echo "❌ No users found in database.\n";
    echo "📝 Please create a user first:\n";
    echo "   php artisan tinker\n";
    echo "   >>> User::factory()->create()\n\n";
    exit(1);
}

echo "✅ User found: {$user->name} ({$user->email})\n\n";

// Test 1: Send Welcome Notification
echo "📧 Test 1: Sending Welcome Notification...\n";
try {
    $user->notify(new WelcomeNotification);
    echo "✅ Notification queued successfully!\n\n";
} catch (\Exception $e) {
    echo '❌ Error: '.$e->getMessage()."\n\n";
    exit(1);
}

// Test 2: Check if notification was stored
echo "💾 Test 2: Checking database storage...\n";
$notifications = $user->notifications;
echo '✅ Total notifications: '.$notifications->count()."\n";
echo '✅ Unread notifications: '.$user->unreadNotifications->count()."\n\n";

// Test 3: Display last notification
if ($notifications->count() > 0) {
    echo "📬 Test 3: Last notification details...\n";
    $lastNotification = $notifications->first();
    echo '   ID: '.$lastNotification->id."\n";
    echo '   Type: '.$lastNotification->type."\n";
    echo '   Created: '.$lastNotification->created_at->diffForHumans()."\n";
    echo '   Read: '.($lastNotification->read_at ? 'Yes' : 'No')."\n";
    echo '   Data: '.json_encode($lastNotification->data, JSON_PRETTY_PRINT)."\n\n";
}

// Test 4: Check notification preferences
echo "⚙️  Test 4: Checking notification preferences...\n";
use App\Models\NotificationPreference;

$preferences = NotificationPreference::where('user_id', $user->id)->get();
if ($preferences->count() > 0) {
    echo '✅ Preferences configured: '.$preferences->count()." types\n";
    foreach ($preferences as $pref) {
        echo "   - {$pref->notification_type}: ";
        echo 'Email='.($pref->channel_email ? '✓' : '✗').' ';
        echo 'Database='.($pref->channel_database ? '✓' : '✗').' ';
        echo 'SMS='.($pref->channel_sms ? '✓' : '✗').' ';
        echo 'Push='.($pref->channel_push ? '✓' : '✗')."\n";
    }
} else {
    echo "ℹ️  No preferences set yet (will use defaults)\n";
}

echo "\n";
echo "🎉 All tests completed successfully!\n";
echo "\n";
echo "📚 Next steps:\n";
echo "   1. Start queue worker: php artisan queue:work\n";
echo "   2. Check email logs: storage/logs/laravel.log\n";
echo "   3. Test API endpoints: see NOTIFICATION_API_GUIDE.md\n";
echo "\n";
