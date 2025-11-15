<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    protected $signature = 'user:create-admin {email} {--name=Admin} {--password=}';

    protected $description = 'Create or update an admin user with the given email and password';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $name = (string) $this->option('name');
        $password = (string) ($this->option('password') ?? '');

        if ($password === '') {
            $this->error('You must provide --password');
            return 1;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt($password),
                'role' => 'admin',
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        $this->info("Admin user ready: {$user->email}");
        return 0;
    }
}
