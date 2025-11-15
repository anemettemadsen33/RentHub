<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PropertyImportService;
use Illuminate\Console\Command;

class TestPropertyImport extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:import {platform=booking} {--user=owner@test.com}';

    /**
     * The console command description.
     */
    protected $description = 'Test property import from external platforms (booking, airbnb, vrbo)';

    /**
     * Execute the console command.
     */
    public function handle(PropertyImportService $importService): int
    {
        $platform = $this->argument('platform');
        $userEmail = $this->option('user');

        // Find user
        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error("User with email '{$userEmail}' not found!");
            return Command::FAILURE;
        }

        $this->info("Testing property import from {$platform}...");
        $this->info("User: {$user->name} ({$user->email})");

        // Test URLs for each platform
        $testUrls = [
            'booking' => 'https://www.booking.com/hotel/ro/luxury-apartment-bucharest.html',
            'airbnb' => 'https://www.airbnb.com/rooms/12345678',
            'vrbo' => 'https://www.vrbo.com/1234567',
        ];

        $url = $testUrls[$platform] ?? null;
        if (!$url) {
            $this->error("Platform '{$platform}' not supported. Use: booking, airbnb, or vrbo");
            return Command::FAILURE;
        }

        $this->info("URL: {$url}");
        $this->newLine();

        // Import property
        $result = $importService->importProperty($platform, $url, $user);

        if ($result['success']) {
            $this->info('✅ Import successful!');
            $this->newLine();
            $this->table(
                ['Field', 'Value'],
                [
                    ['Property ID', $result['property_id'] ?? 'N/A'],
                    ['Message', $result['message']],
                    ['Title', $result['data']->title ?? 'N/A'],
                    ['Type', $result['data']->type ?? 'N/A'],
                    ['Bedrooms', $result['data']->bedrooms ?? 'N/A'],
                    ['Price', $result['data']->price_per_night ?? 'N/A'],
                    ['City', $result['data']->city ?? 'N/A'],
                    ['Country', $result['data']->country ?? 'N/A'],
                ]
            );

            if (isset($result['data']->amenities) && $result['data']->amenities->count() > 0) {
                $this->newLine();
                $this->info('Amenities: ' . $result['data']->amenities->pluck('name')->join(', '));
            }

            return Command::SUCCESS;
        } else {
            $this->error('❌ Import failed!');
            $this->error($result['message']);
            return Command::FAILURE;
        }
    }
}
