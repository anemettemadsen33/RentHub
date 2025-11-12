<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create 
                            {email? : Admin email address}
                            {password? : Admin password}
                            {name? : Admin full name}
                            {--force : Force creation even if user exists}';

    protected $description = 'Create an admin user for Filament admin panel';

    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║         🚀 RentHub - Create Admin User                     ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Get email
        $email = $this->argument('email') ?? $this->ask('Admin email address', 'admin@renthub.com');

        // Validate email
        $validator = Validator::make(['email' => $email], ['email' => 'required|email']);
        if ($validator->fails()) {
            $this->error('❌ Invalid email address!');
            return 1;
        }

        // Check if user exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            if ($this->option('force')) {
                $this->warn("⚠️  User {$email} exists. Updating to admin...");
            } else {
                $this->error("❌ User with email {$email} already exists!");
                
                if ($this->confirm('Update password and make admin?', true)) {
                    $password = $this->argument('password') ?? $this->secret('New password (min 8 chars)');
                    
                    if (strlen($password) < 8) {
                        $this->error('❌ Password must be at least 8 characters!');
                        return 1;
                    }

                    $existingUser->update([
                        'password' => Hash::make($password),
                        'role' => 'admin',
                        'email_verified_at' => now(),
                    ]);

                    $this->newLine();
                    $this->info('✅ User updated to admin successfully!');
                    $this->displayCredentials($email, $password);
                    return 0;
                }
                
                return 1;
            }
        }

        // Get password
        $password = $this->argument('password') ?? $this->secret('Admin password (min 8 characters)');
        
        if (strlen($password) < 8) {
            $this->error('❌ Password must be at least 8 characters!');
            return 1;
        }

        // Get name
        $name = $this->argument('name') ?? $this->ask('Admin full name', 'Admin User');

        // Create admin user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->newLine();
            $this->info('✅ Admin user created successfully!');
            $this->displayCredentials($email, $password);

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Failed to create admin: ' . $e->getMessage());
            return 1;
        }
    }

    private function displayCredentials(string $email, string $password): void
    {
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📧 Email:    ' . $email);
        $this->info('🔑 Password: ' . $password);
        $this->info('🎯 Role:     Administrator');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->info('🌐 Filament Admin Panel:');
        $this->info('   Local:      http://localhost:8000/admin');
        $this->info('   Production: https://renthub-tbj7yxj7.on-forge.com/admin');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        $this->warn('⚠️  Save these credentials securely!');
        $this->newLine();
    }
}
