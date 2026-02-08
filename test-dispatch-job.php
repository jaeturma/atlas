<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Manual Job Dispatch Test ===\n\n";

// Create a unique token
$token = \Illuminate\Support\Str::uuid()->toString();
echo "Token: " . $token . "\n";
echo "Month: 2026-01\n\n";

// Dispatch the job
$job = new App\Jobs\GenerateEmployeeDeductions($token, '2026-01');
dispatch($job);

echo "✓ Job dispatched\n";
echo "\nNow run: php artisan queue:work --once --tries=1 --timeout=60\n";
