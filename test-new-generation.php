<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing New Structure Generation ===\n\n";

$token = Illuminate\Support\Str::uuid()->toString();
$month = '2026-02';

echo "Generating for: {$month}\n";
echo "Token: {$token}\n\n";

// Dispatch the job
$job = new App\Jobs\GenerateEmployeeDeductions($token, $month);
dispatch($job);

echo "Job dispatched, running queue worker...\n\n";

// Run the queue worker
Artisan::call('queue:work', ['--once' => true, '--tries' => 1]);

echo "\n=== Results ===\n\n";

// Check employee_deductions (summary table)
$summaries = App\Models\EmployeeDeduction::where('period_month', '2026-02-01')->get();
echo "Employee Deductions (Summary Table):\n";
echo "Total rows: " . $summaries->count() . "\n\n";

foreach ($summaries as $summary) {
    $emp = $summary->employee;
    echo "{$emp->first_name} {$emp->last_name}:\n";
    echo "  Total Amount: ₱" . number_format($summary->total_amount, 2) . "\n";
    echo "  Status: {$summary->status}\n";
    echo "  Period: {$summary->period_month->format('F Y')}\n\n";
}

// Check employee_deduction_entries (detail table)
$entries = App\Models\EmployeeDeductionEntry::where('period_month', '2026-02-01')->get();
echo "Employee Deduction Entries (Detail Table):\n";
echo "Total rows: " . $entries->count() . "\n";
echo "Breakdown:\n";
$byKind = $entries->groupBy('kind');
foreach ($byKind as $kind => $kindEntries) {
    echo "  - " . ucfirst($kind) . ": " . $kindEntries->count() . " entries\n";
}

echo "\n=== Verification ===\n";
if ($summaries->count() === 6) {
    echo "✅ employee_deductions: 6 rows (one per employee)\n";
} else {
    echo "⚠ employee_deductions: Expected 6, got " . $summaries->count() . "\n";
}

if ($entries->count() === 13) {
    echo "✅ employee_deduction_entries: 13 rows (detailed breakdown)\n";
} else {
    echo "⚠ employee_deduction_entries: Expected 13, got " . $entries->count() . "\n";
}

$totalFromSummary = $summaries->sum('total_amount');
$totalFromEntries = $entries->sum('amount');
echo "\nTotal from summaries: ₱" . number_format($totalFromSummary, 2) . "\n";
echo "Total from entries: ₱" . number_format($totalFromEntries, 2) . "\n";

if ($totalFromSummary == $totalFromEntries) {
    echo "✅ Totals match!\n";
} else {
    echo "⚠ Totals don't match!\n";
}
