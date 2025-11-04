<?php

namespace App\Console\Commands;

use App\Models\GoogleCalendarToken;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RenewGoogleCalendarWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-calendar:renew-webhooks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew expiring Google Calendar webhooks';

    public function __construct(
        private GoogleCalendarService $googleCalendarService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expiring Google Calendar webhooks...');

        // Get webhooks expiring in the next 24 hours
        $expiringTokens = GoogleCalendarToken::where('sync_enabled', true)
            ->where('webhook_expiration', '<=', now()->addDay())
            ->whereNotNull('webhook_id')
            ->get();

        if ($expiringTokens->isEmpty()) {
            $this->info('No expiring webhooks found.');
            return 0;
        }

        $this->info("Found {$expiringTokens->count()} expiring webhooks.");

        $renewed = 0;
        $failed = 0;

        foreach ($expiringTokens as $token) {
            try {
                $this->info("Renewing webhook for token ID: {$token->id}");
                
                // Stop old webhook
                $this->googleCalendarService->stopWebhook($token);
                
                // Setup new webhook
                $this->googleCalendarService->setupWebhook($token);
                
                $renewed++;
                $this->info("✓ Webhook renewed successfully");
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed to renew webhook: {$e->getMessage()}");
                
                Log::error('Failed to renew Google Calendar webhook', [
                    'token_id' => $token->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("\nSummary:");
        $this->info("Renewed: {$renewed}");
        $this->info("Failed: {$failed}");

        return 0;
    }
}
