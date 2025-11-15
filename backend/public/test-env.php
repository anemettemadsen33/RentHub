<?php
echo "Working dir: " . getcwd() . "\n";
echo "APP_ENV from getenv: " . (getenv('APP_ENV') ?: 'not set') . "\n";
echo "APP_ENV from _ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . "\n";

// Bootstrap Laravel to check config
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Config app.env: " . config('app.env') . "\n";
echo "Config database.default: " . config('database.default') . "\n";
echo "Config database.connections.mysql.database: " . config('database.connections.mysql.database') . "\n";
