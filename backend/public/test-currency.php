<?php
// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Currency;
use Illuminate\Support\Facades\DB;

echo "Database: " . DB::connection()->getDatabaseName() . "\n";
echo "Currencies count: " . Currency::count() . "\n";
echo "Active currencies: " . Currency::active()->count() . "\n";
$currencies = Currency::active()->get();
echo "First currency: " . ($currencies->first()?->code ?? 'none') . "\n";
echo json_encode(['data' => $currencies->toArray()]);
