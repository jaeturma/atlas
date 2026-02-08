<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Employee Deduction Generation ===\n\n";

$token = Illuminate\Support\Str::uuid()->toString();
$month = '2026-01';

echo "Dispatching job...\n";
echo "Token: {$token}\n";
echo "Month: {$month}\n\n";

// Dispatch the job
$job = new App\Jobs\GenerateEmployeeDeductions($token, $month);
dispatch($job);

echo "✓ Job dispatched to queue\n";
echo "\nNow run in another terminal:\n";
echo "  php artisan queue:work --once\n\n";
echo "Or run it here automatically? (will block until complete)\n";

// Auto-run the queue worker
echo "Running queue worker...\n";
Artisan::call('queue:work', ['--once' => true, '--tries' => 1]);

echo "\n=== Generation Results ===\n";

// Check the cache for results
$cacheKey = 'deduction_gen_' . $token;
$result = Cache::get($cacheKey);

if ($result) {
    echo "Status: " . ($result['status'] ?? 'unknown') . "\n";
    echo "Processed: " . ($result['processed'] ?? 0) . " employees\n";
    echo "Created: " . ($result['created'] ?? 0) . " entries\n";
    echo "Updated: " . ($result['updated'] ?? 0) . " entries\n";
}

// Verify in database
echo "\n=== Database Verification ===\n";
$entries = App\Models\EmployeeDeductionEntry::where('period_month', '2026-01-01')->get();
echo "Total entries for 2026-01: " . $entries->count() . "\n";

$byKind = $entries->groupBy('kind');
foreach ($byKind as $kind => $kindEntries) {
    echo "  - " . ucfirst($kind) . ": " . $kindEntries->count() . "\n";
}

if ($entries->count() === 13) {
    echo "\n✅ SUCCESS! Generated exactly 13 entries as expected!\n";
} else {
    echo "\n⚠ Expected 13 entries, but got " . $entries->count() . "\n";
}
