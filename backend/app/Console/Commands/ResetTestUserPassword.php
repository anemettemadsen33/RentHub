<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetTestUserPassword extends Command
{
    protected $signature = 'user:reset-password {email} {password}';

    protected $description = 'Reset user password';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User {$email} not found!");

            return 1;
        }

        $user->password = bcrypt($password);
        $user->save();

        $this->info("✅ Password reset successfully for {$email}");

        return 0;
    }
}
