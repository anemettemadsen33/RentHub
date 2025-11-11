<?php

namespace App\Console\Commands;

use App\Services\BeamsClient;
use Illuminate\Console\Command;

class BeamsTest extends Command
{
    protected $signature = 'beams:test {--interest=broadcast} {--title=Test Notification} {--body=Hello from RentHub Beams!}';

    protected $description = 'Send a test web push notification via Pusher Beams to an interest (default: broadcast).';

    public function handle(BeamsClient $beams)
    {
        if (!$beams->isConfigured()) {
            $this->error('Pusher Beams is not configured. Set PUSHER_BEAMS_INSTANCE_ID and PUSHER_BEAMS_SECRET_KEY.');
            return self::FAILURE;
        }

        $interest = (string) $this->option('interest');
        $title = (string) $this->option('title');
        $body = (string) $this->option('body');

        $result = $beams->publishToInterests([$interest], $title, $body);

        if ($result['ok'] ?? false) {
            $this->info("Beams notification sent to interest '{$interest}'.");
            return self::SUCCESS;
        }

        $this->error('Failed to send Beams notification: ' . ($result['error'] ?? 'unknown error'));
        return self::FAILURE;
    }
}
