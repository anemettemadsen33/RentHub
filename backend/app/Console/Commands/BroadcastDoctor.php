<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BroadcastDoctor extends Command
{
    protected $signature = 'broadcast:doctor';

    protected $description = 'Diagnose Laravel broadcasting (Pusher Channels) configuration and connectivity';

    public function handle(): int
    {
        $this->info('🔎 Broadcasting Doctor');

        // .env checks
        $driver = config('broadcasting.default');
        $this->line("Driver: <info>{$driver}</info>");
        if ($driver !== 'pusher') {
            $this->warn("Expected 'pusher'. Set BROADCAST_CONNECTION=pusher in backend/.env");
        }

        $key = config('broadcasting.connections.pusher.key');
        $appId = config('broadcasting.connections.pusher.app_id');
        $cluster = data_get(config('broadcasting.connections.pusher.options'), 'cluster');
        $host = data_get(config('broadcasting.connections.pusher.options'), 'host');
        $scheme = data_get(config('broadcasting.connections.pusher.options'), 'scheme');
        $port = data_get(config('broadcasting.connections.pusher.options'), 'port');

        $this->line('App ID: <info>'.($appId ?: '[missing]').'</info>');
        $this->line('Key: <info>'.($key ?: '[missing]').'</info>');
        $this->line('Cluster: <info>'.($cluster ?: '[mt1 default]').'</info>');
        $this->line('Host: <info>'.($host ?: '[pusher default]').'</info>');
        $this->line('Scheme: <info>'.($scheme ?: 'https').'</info>');
        $this->line('Port: <info>'.($port ?: 443).'</info>');

        $missing = [];
        foreach (['PUSHER_APP_ID', 'PUSHER_APP_KEY', 'PUSHER_APP_SECRET'] as $envKey) {
            if (! env($envKey)) {
                $missing[] = $envKey;
            }
        }
        if ($missing) {
            $this->error('Missing env vars: '.implode(', ', $missing));
            $this->line('Edit backend/.env with your Channels credentials (not Beams).');
        }

        // Try to create a Pusher client and trigger a harmless event to a dummy channel
        try {
            $client = new \Pusher\Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );

            $result = $client->trigger('doctor.test', 'ping', ['time' => now()->toIso8601String()]);
            if ($result === true) {
                $this->info('✅ Pusher API reachable — test event sent to channel doctor.test');
            } else {
                $this->warn('Pusher API call returned non-true result. Check keys/cluster.');
            }
        } catch (\Throwable $e) {
            $this->error('❌ Failed to reach Pusher API: '.$e->getMessage());
            $this->line('Check: keys valid, cluster correct, server has internet access.');
        }

        // Route + auth sanity
        $this->line('Auth endpoint (Echo private channels): POST /broadcasting/auth via Sanctum');
        $this->line('Ensure frontend sends Authorization: Bearer <token>.');

        $this->info('Done.');

        return self::SUCCESS;
    }
}
