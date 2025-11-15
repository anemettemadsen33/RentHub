<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class AdminDiagnose extends Command
{
    protected $signature = 'admin:diagnose {--run-fixes : Print suggested fix commands}';

    protected $description = 'Diagnose Filament admin panel issues in production';

    public function handle(): int
    {
        $this->info('=== Filament Admin Diagnostics ===');

        // 1) Env & App
        $this->line('\n[App & Env]');
        $this->line('APP_ENV:        '.config('app.env'));
        $this->line('APP_DEBUG:      '.(config('app.debug') ? 'true' : 'false'));
        $this->line('APP_URL:        '.config('app.url'));
        $this->line('PHP Version:    '.PHP_VERSION);

        // 2) Panel route registered
        $this->line('\n[Filament Routes]');
        $adminRoute = collect(Route::getRoutes())->first(function ($r) {
            return str_starts_with($r->uri(), 'admin');
        });
        $this->line('Admin route registered: '.($adminRoute ? 'YES ('.$adminRoute->uri().')' : 'NO'));

        // 3) Sessions & storage permissions
        $this->line('\n[Storage & Sessions]');
        $sessionPath = storage_path('framework/sessions');
        $this->line('Session driver:  '.config('session.driver'));
        $this->line('Session path:    '.$sessionPath);
        $this->line('Session writable: '.(is_writable($sessionPath) ? 'YES' : 'NO'));
        $storageWritable = is_writable(storage_path());
        $this->line('storage/ writable: '.($storageWritable ? 'YES' : 'NO'));

        // 4) Database connectivity
        $this->line('\n[Database]');
        try {
            DB::connection()->getPdo();
            $this->line('DB connection:  OK');
            $users = DB::table('users')->count();
            $this->line('Users count:    '.$users);
        } catch (\Throwable $e) {
            $this->error('DB connection:  ERROR - '.$e->getMessage());
        }

        // 5) Migrations status (summary)
        $this->line('\n[Migrations]');
        try {
            Artisan::call('migrate:status', ['--no-interaction' => true]);
            $output = Artisan::output();
            $lines = collect(explode("\n", trim($output)))->take(15);
            foreach ($lines as $line) {
                $this->line($line);
            }
        } catch (\Throwable $e) {
            $this->error('migrate:status failed: '.$e->getMessage());
        }

        // 6) CORS / Session / Sanctum related envs
        $this->line('\n[Security / CORS / Session]');
        $this->line('SESSION_DOMAIN:            '.(env('SESSION_DOMAIN') ?: '(null)'));
        $this->line('SESSION_SECURE_COOKIE:     '.(env('SESSION_SECURE_COOKIE') ? 'true' : 'false'));
        $this->line('SESSION_SAME_SITE:         '.(env('SESSION_SAME_SITE') ?: '(default)'));
        $this->line('SANCTUM_STATEFUL_DOMAINS:  '.(env('SANCTUM_STATEFUL_DOMAINS') ?: '(null)'));
        $this->line('CORS_ALLOWED_ORIGINS:      '.(env('CORS_ALLOWED_ORIGINS') ?: '(null)'));

        // 7) Cache state
        $this->line('\n[Caches]');
        $paths = [
            base_path('bootstrap/cache/config.php'),
            base_path('bootstrap/cache/routes.php'),
            base_path('bootstrap/cache/packages.php'),
        ];
        foreach ($paths as $p) {
            $this->line(basename($p).': '.(File::exists($p) ? 'present' : 'missing'));
        }

        $this->line("\nRun quick fixes (print commands)");
        if ($this->option('run-fixes')) {
            $this->line('> php artisan config:clear');
            $this->line('> php artisan cache:clear');
            $this->line('> php artisan route:clear');
            $this->line('> php artisan view:clear');
            $this->line('> php artisan storage:link');
        }

        $this->info('\nDone. If /admin still fails, set APP_DEBUG=true temporarily and re-check laravel.log');

        return Command::SUCCESS;
    }
}
